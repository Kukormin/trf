<?
namespace Local\Utils;

class Log {

    var $file;

    public function __construct($filename)
    {
	    $this->file = $_SERVER['DOCUMENT_ROOT'] . $filename;
	    $f = fopen($this->file, 'a');
	    fwrite($f, "\n");
	    fclose($f);
    }

	public function writeText($text)
	{
		$f = fopen($this->file, 'a');
		fwrite($f, date('H:i:s'));
		fwrite($f, "\t");
		fwrite($f, $text);
		fwrite($f, "\n");
		fclose($f);
	}

}
