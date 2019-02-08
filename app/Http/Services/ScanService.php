<?php

namespace App\Http\Services;

use App\Http\Repositories\ScanRepository;
use App\Http\Repositories\TranslationRepository;
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
        }
        $id = $this->scanRepository->create($result);

        return $id;
    }

    public function getResultByID($id)
    {
        return $this->scanRepository->getById($id);
    }
}