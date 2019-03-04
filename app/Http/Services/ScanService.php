<?php

namespace App\Http\Services;

use App\Http\Repositories\ScanRepository;
use App\Http\Repositories\TranslationRepository;
use App\Models\Type;
use Carbon\Carbon;
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
        $result['time'] = time();
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
            $scan_result->each(function ($item, $key) use ($types, $systems) {
                $temp = explode("\t", $item);
                $types->put($temp[2], $types->get($temp[2], 0) + 1);
                $system_name = str_replace(' (Docked)','',$temp[1]);
                $systems->put($system_name, $systems->get($system_name, 0) + 1);
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

            $product = $this->generateTypeAndGroupFromArray($scan_result);

            $response = [];
            $response ['scan_type'] = $result['type'];
            $response ['groups'] = $product['groups']->toArray();
            $response ['types'] = $product['types']->toArray();
            $response ['translation'] = [
                'group' => $product['group_t']->toArray(),
                'type' => $product['type_t']->toArray(),
            ];
        } elseif ($result['type'] == 'fleet_scan') {
            $response = [];
            $response['scan_type'] = $result['type'];
            $product = $this->generateTypeAndGroupFromArray($result['ships']);
            $response ['groups'] = $product['groups']->toArray();
            $response ['types'] = $product['types']->toArray();
            $response ['translation'] = [
                'group' => $product['group_t']->toArray(),
                'type' => $product['type_t']->toArray(),
            ];
            $response['systems'] = $result['systems'];
        } elseif ($result['type'] == 'local_scan') {

        }

        //$response['create_time'] = (new Carbon($result['time']))->toDateTimeString();

        return $response;
    }

    private function generateTypeAndGroupFromArray($array)
    {
        $types = Type::whereIn('typeID', array_keys($array))->with('group')->get()->keyBy('typeID')->filter(function (
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
        foreach ($array as $key => $value) {
            if ($types->has($key)) {
                $groupID = $types->get($key)->group->groupID;
                $groups->put($groupID, $groups->get($groupID, 0) + $value);
                $types_result->put($key, ['id'=>$key,'groupID'=>$groupID,'amount'=>$value]);
            }
        }
        $group_translation = $this->translationRepository->getTranslation(TranslationRepository::GROUP, $groups->keys());
        $type_translation = $this->translationRepository->getTranslation(TranslationRepository::TYPE, $types->keys());

        return [
            'types' => $types_result,
            'groups' => $groups,
            'group_t' => $group_translation,
            'type_t' => $type_translation,
        ];
    }
}