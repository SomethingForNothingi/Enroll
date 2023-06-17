<?php

namespace App\Http\Controllers;

use App\Models\Apply;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{

    //
    public function getColumns()
    {
        $sql = "SELECT column_name,column_comment FROM information_schema.columns where table_name='student' or table_name='apply'";
        $columns = DB::select($sql);
        $data = [];
        $forget = [ 'password', 'updated_at' ];
        foreach ($columns as $v) {
            if (in_array($v->column_name, $forget)) {
                continue;
            }
            $data['columns'][$v->column_name] = $v->column_comment;
            $data['order'][] = $v->column_name;
        }

        return $this->returnData(0, '', $data);

    }

    // 列表数据
    public function getList(Request $request)
    {
        $params = $request->all();
        $excel = $params['excel'] ?? 0;
        $page = $params['page'] ?? 1;
        $pageSize = $params['per_page'] ?? 10;

        if ($excel) {
            $data = Apply::query()->with(['student'])->orderBy('batch')->orderBy('batch_rank')->get()->toArray();
            foreach ($data as $k => &$v) {
                $v['apply'] = IndexController::SCHOOL[$v['apply']];
            }
            $data = $this->dataRebuild($data);
        } else {
            $data = Apply::query()->with(['student'])->search($params)->orderBy('batch')->orderBy('batch_rank')->paginate($pageSize)->appends([ 'current_page' => $page ])->toArray();
            foreach ($data['data'] as $k => &$v) {
                $v['apply'] = IndexController::SCHOOL[$v['apply']];
            }
            $data['data'] = $this->dataRebuild($data['data']);
        }

        return $this->returnData(self::OK, '', $data);
    }

    public function dataRebuild($data): array
    {
        $newData = [];
        foreach ($data as $k1 => $v1) {
            foreach ($v1 as $k2 => $v2) {
                if ($k2 == 'student') {
                    foreach ($v2 as $k3 => $v3) {
                        if ($k3 == 'id' || $k3 == 'password') {
                            continue;
                        }
                        $newData[$k1][$k3] = $v3;
                    }
                } else {
                    $newData[$k1][$k2] = $v2;
                }
            }

        }
        return $newData;
    }

    // 破格录取
    public function admission(Request $request): array
    {
        $id = $request->input('id');
        $apply = $request->input('apply');
        Apply::query()->find($id)->update([ 'success' => 1, 'apply' => $apply ]);
        return $this->returnData(self::OK, '');
    }

    public function logout(): array
    {
        Auth::guard(self::GUARD_ADMIN)->logout();
        return $this->returnData(self::OK);
    }
}
