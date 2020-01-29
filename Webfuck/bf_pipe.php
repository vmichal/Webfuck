<?php

class bf_pipe {

	private $io_pipes_;
	private $process_handle_ ;

	function __construct() {
		////debug_print_backtrace();

		$this->process_handle_ = proc_open(__DIR__ . '/emulator/Brainfuck', array (
			0 => array('pipe', 'r'),
			1 => array('pipe', 'w'),
			2 => array('pipe', 'w')
			), $this->io_pipes_);

		assert(stream_set_read_buffer($this->io_pipes_[1], 0) == 0);
		assert(stream_set_read_buffer($this->io_pipes_[2], 0) == 0);

		if (!is_resource($this->process_handle_))
			die('Failed');
		////echo __FUNCTION__ . PHP_EOL;
	}

	function __destruct() {
		////debug_print_backtrace();

		if (isset($this->process_handle_))
			$this->close();
		////echo __FUNCTION__ . PHP_EOL;

	}

	function execute_command($command) {
		////debug_print_backtrace();

		fwrite($this->io_pipes_[0], $command . "\n");
		fflush($this->io_pipes_[0]);
		////echo __FUNCTION__ . PHP_EOL;

	}

	function read_output() : string {
		////debug_print_backtrace();
		$string = fread($this->io_pipes_[1], 8196);
		//stream_set_blocking($this->io_pipes_[1], FALSE);
		if (strpos($string, '(b-fuck)') === FALSE) { //if we didn't read all the data
			$except = null;
			$write =null;
			while (true) {
				$read = array($this->io_pipes_[1]);
				////print_r(
				stream_select($read, $write, $except, 1)
					////)
					;
				////print_r($read);
				$str = fread($this->io_pipes_[1], 1024);
				$string .= $str;
				////var_dump($str);
				echo PHP_EOL;
				if (strpos($string, '(b-fuck)', - ((strlen($string) < 1024) ? strlen($string) : 1024)) !== FALSE)
					break;
			}
		}
		//stream_set_blocking($this->io_pipes_[1], TRUE);
		////var_dump($string);
		////echo __FUNCTION__ . PHP_EOL;
		return $string;
	}

	function stderr() : string { 
		////debug_print_backtrace();
		$string = fread($this->io_pipes_[2], 65566);
		////var_dump($string);
		////echo __FUNCTION__ . PHP_EOL;
		return $string;
	}

	function close() {
		////debug_print_backtrace();

		fwrite($this->io_pipes_[0], "dont-repeat\ndont-repeat\nquit\n");
		fflush($this->io_pipes_[0]);
		foreach ($this->io_pipes_ as $pipe)
			fclose($pipe);
		proc_close($this->process_handle_);
		unset($this->io_pipes_);
		unset($this->process_handle_);
		////echo __FUNCTION__ . PHP_EOL;
	}

	function clear_pipes() {
		////debug_print_backtrace();
		fflush($this->io_pipes_[0]);

		$read = [$this->io_pipes_[1], $this->io_pipes_[2]];
		$write = null;
		$except = null;
		
		while ($read) {
			////var_dump($read);
			////var_dump(
			stream_select($read, $write, $except, 1)
			////)
			;
			foreach ($read as $remaining_garbage) {
				////echo "DUMPING\n";
				////var_dump(
				fread($remaining_garbage, 8196)
				////)
				;
			}

		}
		////echo __FUNCTION__ . PHP_EOL;
	}
}