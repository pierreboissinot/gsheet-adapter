<?php

namespace Translation\PlatformAdapter\Sheet;

use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_ValueRange;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Translation\Common\Model\Message;
use Translation\Common\Storage;
use Translation\Common\TransferableStorage;

class Sheet implements Storage, TransferableStorage
{
    /**
     * @var Google_Client
     */
    private $client;

    /**
     * @var Google_Service_Sheets
     */
    private $sheetService;

    private $spreadsheetId = '1KrInPpO_oXs99GBjiwYRvz_y9sPiO1aeV_IxSflT7aQ';

    public function __construct(Google_Client $client)
    {
        $this->client = $client;
        $this->sheetService = new Google_Service_Sheets($this->client);
    }

    /**
     * {@inheritdoc}
     */
    public function get($locale, $domain, $key)
    {
        $keyIndex = $this->getKeyIndex($key);
        if (null === $keyIndex) {
            return null;
        }
        $range = 'translations_'.$locale;
        $response = $this->sheetService->spreadsheets_values->get($this->spreadsheetId, $range, ['majorDimension' => 'COLUMNS']);
        $values = $response->getValues();

        var_dump($keyIndex);
        var_dump($values);
        if (0 == count($values) || count($values) <= $keyIndex) {
            printf('traduction non trouvée');

            return null;
        }   // first row first column: get a Cell
        $translation = $values[0][$keyIndex];

        return new Message($key, $domain, $locale, $translation, $meta = []);
    }

    /**
     * {@inheritdoc}
     */
    public function create(Message $message)
    {
        // if a translation exists do nothing
        $messageFound = $this->get($message->getLocale(), $message->getDomain(), $message->getKey());
        if (null === $messageFound) {
            // create a new translation or asset ?
            // create key ?
            if (null === $this->getKeyIndex($message->getKey())) {
                $range = 'clefs';
                $valueInputOption = 'RAW';
                $values = [
                    [$message->getKey()],
                ];
                $body = new Google_Service_Sheets_ValueRange([
                    'values' => $values,
                ]);
                $params = [
                    'valueInputOption' => $valueInputOption,
                ];
                $result = $this->sheetService->spreadsheets_values->append($this->spreadsheetId, $range,
                    $body, $params);
                printf('%d cells appended.', $result->getUpdates()->getUpdatedCells());
            }
        }
        // create translation
        $this->update($message);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Message $message)
    {
        $valueInputOption = 'RAW';
        // TODO: manage conflict keys
        $key = $message->getKey();
        $locale = $message->getLocale();
        $translation = $message->getTranslation();
        $range = 'translations_'.$locale;
        $keyIndex = $this->getKeyIndex($key);
        var_dump($keyIndex);

        $values = [];
        $values[] = $translation;

        $rows = [];
        for ($i = 0; $i < $keyIndex; ++$i) {
            $rows[] = [];
        }
        $rows[] = $values;

        $body = new Google_Service_Sheets_ValueRange([
            'values' => $rows, //empty array for ignore any change
        ]);
        $params = [
            'valueInputOption' => $valueInputOption,
        ];
        $result = $this->sheetService->spreadsheets_values->update(
            $this->spreadsheetId,
            $range,
            $body,
            $params
        );
        printf('%d cells updated.', $result->getUpdatedCells());
    }

    /**
     * {@inheritdoc}
     */
    public function delete($locale, $domain, $key)
    {
        // According to GSheet API v4: "To clear data, use an empty string ("")."
        $this->update(new Message($key, $domain, $locale, ''));
    }

    /**
     * {@inheritdoc}
     */
    public function export(MessageCatalogueInterface $catalogue)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function import(MessageCatalogueInterface $catalogue)
    {
        // TODO: Implement import() method.
    }

    /**
     * Retourne l'index correspondant à la clef dans la sheet
     * keyindex 1 correspond à la 3eme ligne (voire les plages)
     *
     * @param string $key
     *
     * @return int
     */
    private function getKeyIndex(string $key)
    {
        // get key index
        $keyIndex = null;
        $range = 'clefs';
        $response = $this->sheetService->spreadsheets_values->get($this->spreadsheetId, $range, ['majorDimension' => 'COLUMNS']);
        $values = $response->getValues();
        $keys = $values[0];

        if (0 == count($keys)) {
            echo "Cette clef n'existe pas.\n";
        } else {
            for ($i = 0; $i < count($keys); ++$i) {
                if ($keys[$i] === $key) {
                    $keyIndex = $i; // cuz index on sheet starts at 1 not 0 and first row of data_plage is not included
                }
            }
        }

        return $keyIndex;
    }
}
