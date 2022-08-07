<?php

namespace App\Utils\Helpers;
// use League\Flysystem\FilesystemAdapter;

use Carbon\Carbon;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;

class LegacyFtpStorage
{
	public $ftpConnection;

	function __construct($serverLogin)
	{
		$this->ftpConnection = ftp_connect($serverLogin['host'], $serverLogin['port']);
		ftp_login($this->ftpConnection, $serverLogin['username'], $serverLogin['password']);
	}

	public function listContents($directory)
	{
		$data = ftp_mlsd($this->ftpConnection, $directory);

		$data = array_map(function($item) use ($directory) {
			$item["path"] = $directory."/".$item["name"];
			$item["fileSize"] = (int)$item["size"];

			$year = substr($item["modify"], 0, 4);
			$month = substr($item["modify"], 4, 2);
			$day = substr($item["modify"], 6, 2);
			$hour = substr($item["modify"], 8, 2);
			$minute = substr($item["modify"], 10, 2);
			$second = substr($item["modify"], 12, 2);
			$time = Carbon::create($year, $month, $day, $hour, $minute, $second);
			$item["lastModified"] = $time->getTimestampMs();

			return $item;
		}, $data);

		return $data;
	}
}
