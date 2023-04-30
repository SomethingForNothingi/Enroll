<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
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
        $handle = Student::query()->search($params);

        $handle->rightJoin('apply', 'student.card_id', 'apply.card_id')->orderByDesc('student.total_score');
        if ($excel) {
            $data = $handle->get()->toArray();
        } else {
            $data = $handle->paginate($pageSize)->appends([ 'current_page' => $page ])->toArray();
        }
        return $this->returnData(self::OK, '', $data);
    }
}
