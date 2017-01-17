<?php

namespace App\Http\Controllers\Legal;

use App\Contracts\Repository\Legal\TermAndConditionContract;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class TermAndConditionController extends Controller
{
    protected $request;
    protected $termAndConditionRepo;

    public function __construct(Request $request, TermAndConditionContract $termAndConditionContract)
    {
        $this->request = $request;
        $this->termAndConditionRepo = $termAndConditionContract;
    }

    public function index()
    {
        if ($this->request->ajax()) {
            $termsAndConditions = $this->termAndConditionRepo->all();
            $status = true;
            if ($this->request->wantsJson()) {
                return response()->json(compact(['status', 'termsAndConditions']));
            } else {
                return view('legal.term_and_condition.index');
            }
        } else {
            return view('legal.term_and_condition.index');
        }
    }

    public function show($id)
    {
        if ($id == 0) {
            if ($this->request->ajax()) {
                if ($this->request->wantsJson()) {
                    $tnc = $this->termAndConditionRepo->getActive();
                    $status = !is_null($tnc);
                    return response()->json(compact(['status', 'tnc']));
                } else {
                    return view('legal.tnc_pp');
                }
            } else {
                if ($this->request->has('callback')) {
                    $tnc = $this->termAndConditionRepo->getActive();
                    $status = !is_null($tnc);
                    return response()->json(compact(['status', 'tnc']))->setCallback($this->request->get('callback'));
                } else {
                    return view('legal.tnc_pp');
                }
            }
        } else {

        }
    }

    public function edit($id)
    {
        $termAndCondition = $this->termAndConditionRepo->get($id);
        if (is_null($termAndCondition)) {
            abort(404);
            return false;
        }
        return view('legal.term_and_condition.edit')->with(compact(['termAndCondition']));
    }
}
