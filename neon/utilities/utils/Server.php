<?php

namespace neon\utilities\utils;

class Server
{
	public function getMemoryInfo() {
		$memoryInfo = [
			'total' => 0,
			'free' => 0,
			'available' => 0,
			'used' => 0,
			'swap_total' => 0,
			'swap_free' => 0,
			'swap_used' => 0
		];
	
		if (PHP_OS_FAMILY == 'Linux') {
			$memoryInfo = $this->linuxGetMemoryInfo();
		} elseif (PHP_OS_FAMILY == 'Darwin') {
			$memoryInfo = $this->macGetMemoryInfo();
		}
	
		$memoryInfo['used'] = $memoryInfo['total'] - $memoryInfo['free'];
		$memoryInfo['swap_used'] = $memoryInfo['swap_total'] - $memoryInfo['swap_free'];
	
		return $memoryInfo;
	}
	
	public function linuxGetMemoryInfo() {
		$data = file_get_contents('/proc/meminfo');
		$memoryInfo = [
			'total' => 0,
			'free' => 0,
			'available' => 0,
			'swap_total' => 0,
			'swap_free' => 0
		];
	
		if ($data) {
			$lines = explode("\n", $data);
			foreach ($lines as $line) {
				if (preg_match('/^MemTotal:\s+(\d+)\s+kB/', $line, $matches))
					$memoryInfo['total'] = intval($matches[1]) * 1024;
				if (preg_match('/^MemFree:\s+(\d+)\s+kB/', $line, $matches))
					$memoryInfo['free'] = intval($matches[1]) * 1024;
				if (preg_match('/^MemAvailable:\s+(\d+)\s+kB/', $line, $matches))
					$memoryInfo['available'] = intval($matches[1]) * 1024;
				if (preg_match('/^SwapTotal:\s+(\d+)\s+kB/', $line, $matches))
					$memoryInfo['swap_total'] = intval($matches[1]) * 1024;
				if (preg_match('/^SwapFree:\s+(\d+)\s+kB/', $line, $matches))
					$memoryInfo['swap_free'] = intval($matches[1]) * 1024;
			}
		}
	
		return $memoryInfo;
	}
	
	public function macGetMemoryInfo() {
		putenv('PATH=/usr/sbin:/usr/bin:/bin');
		$memoryInfo = [
			'total' => intval(shell_exec("sysctl -n hw.memsize")),
			'free' => 0,
			'available' => 0,
			'swap_total' => 0,
			'swap_free' => 0
		];
	
		$freePages = intval(shell_exec("vm_stat | grep free | awk '{print $3}' | sed 's/\\.$//'"));
		$inactivePages = intval(shell_exec("vm_stat | grep inactive | awk '{print $3}' | sed 's/\\.$//'"));
		$freeMemory = ($freePages + $inactivePages) * 4096; // page size in bytes
	
		$swapUsage = shell_exec("sysctl -n vm.swapusage | awk '{print $3, $10}'");
		list($swapTotal, $swapFree) = explode(" ", $swapUsage);
	
		$memoryInfo['free'] = $freeMemory;
		$memoryInfo['available'] = $freeMemory;
		$memoryInfo['swap_total'] = floatval($swapTotal) * 1024 * 1024; // Convert MB to bytes
		$memoryInfo['swap_free'] = floatval($swapFree) * 1024 * 1024; // Convert MB to bytes
	
		return $memoryInfo;
	}


	function getCpuInfo() {
		$cpuInfo = [
			'model' => '',
			'cores' => 0
		];
	
		if (PHP_OS_FAMILY == 'Linux') {
			$cpuInfo = $this->parseLinuxCpuInfo();
		} elseif (PHP_OS_FAMILY == 'Darwin') {
			$cpuInfo = $this->parseMacCpuInfo();
		}
	
		return $cpuInfo;
	}
	
	function parseLinuxCpuInfo() {
		$cpuInfo = [
			'model' => '',
			'cores' => 0
		];
	
		$data = file_get_contents('/proc/cpuinfo');
		if ($data) {
			$lines = explode("\n", $data);
			foreach ($lines as $line) {
				if (preg_match('/^model name\s+:\s+(.+)/', $line, $matches)) {
					$cpuInfo['model'] = $matches[1];
				}
				if (preg_match('/^cpu cores\s+:\s+(\d+)/', $line, $matches)) {
					$cpuInfo['cores'] = intval($matches[1]);
				}
			}
		}
	
		// Fallback for 'cpu cores' if not found
		if ($cpuInfo['cores'] == 0) {
			$cpuInfo['cores'] = intval(shell_exec('nproc'));
		}
	
		return $cpuInfo;
	}
	
	function parseMacCpuInfo() {
		putenv('PATH=/usr/sbin:/usr/bin:/bin');
		$cpuInfo = [
			'model' => '',
			'cores' => 0
		];
	
		$cpuInfo['model'] = trim(shell_exec("sysctl -n machdep.cpu.brand_string"));
		$cpuInfo['cores'] = intval(shell_exec("sysctl -n hw.ncpu"));
	
		return $cpuInfo;
	}
	

}