<?php

namespace App\Traits;

use App\Models\Sequence;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait SequenceTrait
{
	public function getSequence($code = null)
	{
		try {
			$data = Sequence::where('name', $code)->first('value');
			if ($data) {
				return ['value' => $data->value];
			} else {
				return $this->setSequence($code);
			}
		} catch (QueryException $e) {
			Log::error($e->getMessage());
			return response()->json('Server Error', 500);
		}
	}

	public function setSequence($code = null)
	{
		try {
			DB::beginTransaction();
			$data = Sequence::where('name', $code)->first('value');
			if ($data) {
				$sequence = Sequence::where('name', $code)
					->update(['value' => $data->value + 1]);
				if ($sequence) {
					DB::commit();
					return  ['value' => $data->value + 1];
				}
			} else {
				$sequence = Sequence::create([
					'name' => $code,
					'value' => 1,
				]);
				if ($sequence) {
					DB::commit();
					return ['value' => $sequence->value];
				}
			}
			
		} catch (QueryException $e) {
			DB::rollback();
			Log::error($e->getMessage());
			return response()->json('Server Error', 500);
		}
	}
}
