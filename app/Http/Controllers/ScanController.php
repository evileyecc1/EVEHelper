<?php
/**
 * Created by PhpStorm.
 * User: evileyecc
 * Date: 2019/1/9
 * Time: 5:16
 */

namespace App\Http\Controllers;

use App\Http\Services\ScanService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ScanController extends Controller
{
    private $scanService;

    public function __construct(ScanService $scanService)
    {
        $this->scanService = $scanService;
    }

    public function create(Request $request)
    {
        $result = $request->input('scan');
        $result = new Collection(explode("\n", $result));
        if (is_null($result)) {
            return response()->json(['message' => '提交的内容不能为空'])->setStatusCode(422);
        }
        $scan_type = $this->scanService->getScanType($result);
        if ($scan_type == 'unknown') {
            return response()->json(['message' => '请提交正确的扫描结果'])->setStatusCode(422);
        }

        $id = $this->scanService->storeScanResult($scan_type, $result);

        return response()->json(['scan_id' => $id, 'scan_type' => $scan_type]);
    }

    public function show($id)
    {
        dd($this->scanService->getResultByID($id));
    }
}