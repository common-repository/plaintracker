<?php
namespace Plainware;

class Setting_Time
{
	public $setting = Setting::class;

	public function timeFormat()
	{
		return $this->setting->get( 'time_time_format' );
	}

	public function dateFormat()
	{
		return $this->setting->get( 'time_date_format' );
	}

	public function weekStartsOn()
	{
		return $this->setting->get( 'time_week_starts' );
	}

	public function timezone()
	{
		return $this->setting->get( 'time_timezone' );
	}
}