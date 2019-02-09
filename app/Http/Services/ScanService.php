<?php

namespace App\Http\Services;

use App\Http\Repositories\ScanRepository;
use App\Http\Repositories\TranslationRepository;
use App\Models\Type;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;

class ScanService
{
    private $scanRepository;

    private $translationRepository;

    public function __construct(ScanRepository $scanRepository, TranslationRepository $translationRepository)
    {
        $this->scanRepository = $scanRepository;
        $this->translationRepository = $translationRepository;
    }

    public function getScanType(Collection $scan_result)
    {
        $tab_count = substr_count($scan_result->first(), "\t");
        switch ($tab_count) {
            case 0:
                return 'local_scan';
            case 3:
                return 'dscan';
            case 6:
                return 'fleet_scan';
            default:
                return 'unknown';
        }
    }

    public function storeScanResult($scan_type, Collection $scan_result)
    {
        $result = [];
        $result['type'] = $scan_type;
        if ($scan_type == 'dscan') {
            $counts = collect();
            $scan_result->each(function ($item, $index) use ($counts) {
                $temp = explode("\t", $item);
                $counts->put($temp[0], $counts->get($temp[0], 0) + 1);
            });
            $result['result'] = $counts->toArray();
        } elseif ($scan_type == 'fleet_scan') {
            $types = collect();
            $systems = collect();
            $result->each(function ($item, $key) use ($types, $systems) {
                $temp = explode("\t", $item);
                $types->put($temp[2], $types->get($temp[2], 0) + 1);
                $systems->put($temp[1], $systems->get($temp[1], 0) + 1);
            });
            $texts = $types->keys();
            $ids = $this->translationRepository->convertTextToID(TranslationRepository::TYPE, $texts)->keyBy('text');
            $id_types = $types->mapWithKeys(function ($item, $key) use ($ids) {
                if ($ids->has($key)) {
                    return [$ids->get($key)->keyID => $item];
                }
            });

            $result['systems'] = $systems->toArray();
            $result['ships'] = $id_types->toArray();
        } elseif ($scan_type == 'local_scan') {
            $client = new Client(['base_uri' => 'https://esi.evetech.net']);
            $character_ids = collect();
            $corporations = collect();
            $alliances = collect();
            foreach ($scan_result->chunk(1000) as $chunk) {
                $response = $client->request('post', '/legacy/universe/ids/', ['body' => $chunk->toJson()]);
                $content = json_decode($response->getBody()->getContents(), true);
                foreach ($content['characters'] as $character_info) {
                    $character_ids->push($character_info['id']);
                }
            }
            foreach ($character_ids->chunk(1000) as $ids) {
                $response = $client->request('post', '/legacy/characters/affiliation/', ['body' => $ids->toJson()]);
                $content = json_decode($response->getBody()->getContents());
                foreach ($content as $item) {
                    $corporations->put($item->corporation_id, $corporations->get($item->corporation_id, 0) + 1);
                    if (property_exists($item, 'alliance_id')) {
                        $alliances->put($item->alliance_id, $alliances->get($item->alliance_id, 0) + 1);
                    }
                }
            }
            $result['alliances'] = $alliances->toArray();
            $result['corporations'] = $alliances->toArray();
        }
        $id = $this->scanRepository->create($result);

        return $id;
    }

    public function getResultByID($id)
    {
        $result = $this->scanRepository->getById($id);

        if ($result == false) {
            return false;
        }

        if ($result['type'] == 'dscan') {
            $scan_result = $result['result'];
            $types = Type::whereIn('typeID', array_keys($scan_result))->with('group')->get()->keyBy('typeID')->filter(function (
                $item,
                $key
            ) {
                return in_array($item->group->categoryID, [
                    6,
                    22,
                ]);
            });
            $groups = collect();
            $types_result = collect();
            foreach ($scan_result as $key => $value) {
                if ($types->has($key)) {
                    $groupID = $types->get($key)->group->groupID;
                    $groups->put($groupID, $groups->get($groupID, 0) + $value);
                    $types_result->put($key, $value);
                }
            }
            $group_translation = $this->translationRepository->getTranslation(TranslationRepository::GROUP, $groups->keys());
            $type_translation = $this->translationRepository->getTranslation(TranslationRepository::TYPE, $types->keys());

            $response = [];
            $response ['scan_type'] = $result['type'];
            $response ['groups'] = $groups->toArray();
            $response ['types'] = $types_result->toArray();
            $response ['translation'] = [
                'group' => $group_translation->toArray(),
                'type' => $type_translation->toArray(),
            ];
        }

        return $response;
    }
}