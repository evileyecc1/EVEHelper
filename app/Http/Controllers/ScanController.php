<?php
/**
 * Created by PhpStorm.
 * User: evileyecc
 * Date: 2019/1/9
 * Time: 5:16
 */

namespace App\Http\Controllers;

use App\Http\Repositories\EVE\AllianceRepository;
use App\Http\Services\ScanService;
use App\Utils\ESIHelper;
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
        $result = str_replace("\r\n","\n",$result);
        $result = str_replace("\r","\n",$result);
        if ($result == '') {
            return response()->json(['请不要提交空的结果'])->setStatusCode(422);
        }
        $result = new Collection(explode(PHP_EOL, $result));
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
        $result = $this->scanService->getResultByID($id);

        if($result == false){
            return response()->json(['message'=>'无法找到此扫描结果'])->setStatusCode(404);
        }
        return response()->json($result);
    }

}