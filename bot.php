<?php

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com>
 * @license MIT
 * @version 0.0.1
 */

define("TOKEN", "466965678:AAGVOQjxD9zgOhw-KLZT6vAP1mSztE7y69U");


$app = new LemonIce(
			[
				"mode" => "poll"
			]
		);


$app->run();



















class Bot
{
	/**
	 * @param string $method
	 * @param string $param
	 * @return mixed
	 */
	public static function __callStatic($method, $param)
	{
		$param[0] = isset($param[0]) ? $param[0] : [];
		$post 	  = ! (isset($param[1]) and strtolower($param[1]) == "get");
		if ($post) {
			$ch = curl_init("https://api.telegram.org/bot".TOKEN."/".$method);
			$opt = [
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_SSL_VERIFYPEER => false,
					CURLOPT_SSL_VERIFYHOST => false,
					CURLOPT_POSTFIELDS	   => http_build_query($param[0]),
					CURLOPT_POST	=> true
				];
		} else {
			$ch = curl_init("https://api.telegram.org/bot".TOKEN."/".$method."?".http_build_query($param[0]));
			$opt = [
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_SSL_VERIFYPEER => false,
					CURLOPT_SSL_VERIFYHOST => false
				];
		}
		curl_setopt_array($ch, $opt);
		$out = curl_exec($ch);
		$err = curl_error($ch) and ($out = $err xor error_log($err));
		return $out;
	}
}

class LemonIce
{

	/**
	 * @var string
	 */
	private $mode;

	/**
	 * @var array
	 */
	private $shouldResponse = [];

	/**
	 * @var bool
	 */
	private $needResponse = false;

	/**
	 * Constructor.
	 *
	 * @param array $construct
	 */
	public function __construct($construct)
	{
		init();
		$this->mode = $construct['mode'];
	}

	/**
	 * Run lemon juice.
	 */
	public function run()
	{
		if ($this->mode === "poll") {
			$this->poll_run();
		} else {
			$this->webhook_run();
		}
	}

	private function saveUpdateId($update_id)
	{

	}

	private function poll_run()
	{
		$arr = json_decode(Bot::getUpdates(), true);
		foreach ($arr['result'] as $val) {
			if (isset($val['message']['text'])) {
				$this->needResponse = true;
				$this->shouldResponse[] = $val;
				print $val['update_id']."\n".substr($val['message']['text'], 0, 30).
				(strlen($val['message']['text']) > 30 ? "..." : "" ) . PHP_EOL;
			}
		}
		Bot::getUpdates(
			[
				"offset" => -1
			]
		);
		$this->response();
		$this->poll_run();
	}

	private function response()
	{
		foreach ($this->shouldResponse as $val) {
			$tr = explode(" ", $val['message']['text'], 4);			
			if (count($tr) > 1 and in_array(strtolower($tr[0]), 
				[
					"/tl",
					"~tl",
					"!tl",
					"tl",
					"translate",
					"/translate",
					"!translate",
					"~translate",
				]
			)) {
				if ($tr[1]!=="auto" and strlen($tr[2])!==2) {
					$msg = $val['message']['chat']['type'] === "private" ? "Error!" : null;
					var_dump(123);
				} elseif (
					($tr[1]==="auto" and strlen($tr[2])) or
					(strlen($tr[1]) === 2 and strlen($tr[2]) === 2)
				) {
					$st = new GoogleTranslate($tr[3], $tr[1], $tr[2]);
					$msg = $st->exec();
				} else {
					$msg = $val['message']['chat']['type'] === "private" ? "Error!" : null;
				}
				isset($msg) and print Bot::sendMessage(
					[
						"text" 	  => $st->exec(),
						"chat_id" => $val['message']['chat']['id']
					]
				);
			}
		}
	}
}

/**
 * GoogleTranslate
 * https://github.com/TeaInside/tea-inside-bot/blob/master/src/App/Translator/GoogleTranslate/GoogleTranslate.php
 *
 * @author Ammar Faizi <ammarfaizi2@gmail.com>
 * @license MIT
 * @version 0.0.1
 */
final class GoogleTranslate
{
    /**
     * @var string
     */
    private $text;

    /**
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $to;

    /**
     * @var string
     */
    private $cookiefile;

    /**
     * Constructor.
     *
     * @param string $text
     * @param string $from
     * @param string $to
     */
    public function __construct($text, $from, $to)
    {
        $this->text = $text;
        $this->from = $from;
        $this->to   = $to;
    }

    /**
     * Init google translate dir.
     */
    private function __init()
    {
        if (! is_dir(data."/google_translate")) {
            shell_exec("mkdir -p ".data."/google_translate");
            if (! is_dir(data."/google_translate")) {
                throw new \Exception("Cannot create directory.", 1);
            }
        }
    }

    /**
     * Exec.
     *
     * @return string
     */
    public function exec()
    {
        $this->__init();
        /*$ch = new Curl("https://translate.google.com/m?hl=en&sl=".urlencode($this->from)."&tl=".urlencode($this->to)."&ie=UTF-8&prev=_m&q=".urlencode($this->text));
        $ch->set_opt(
            [
                CURLOPT_COOKIEFILE => data."/google_translate/cookies.ck",
                CURLOPT_COOKIEJAR  => data."/google_translate/cookies.ck"
            ]
        );
        $out = $ch->exec();
        if ($ch->errno) {
            $out = $ch->error xor trigger_error($out);
        } else {
            self::parseOutput($out);
        }*/
        $ch = curl_init("https://translate.google.com/m?hl=en&sl=".urlencode($this->from)."&tl=".urlencode($this->to)."&ie=UTF-8&prev=_m&q=".urlencode($this->text));
        curl_setopt_array($ch, 
        	[
        		CURLOPT_RETURNTRANSFER => true,
        		CURLOPT_SSL_VERIFYPEER => false,
        		CURLOPT_SSL_VERIFYHOST => false,
        		CURLOPT_COOKIEFILE => data."/google_translate/cookies.ck",
                CURLOPT_COOKIEJAR  => data."/google_translate/cookies.ck",
                CURLOPT_HTTPHEADER => [
                	"User-Agent:Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:46.0) Gecko/20100101 Firefox/46.0",
                	"Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
                	"Accept-Language:en-US,en;q=0.5",
                	"Connection:keep-alive",
                	"Cache-Control:max-age=0"
                ]
        	]
        );
        $out = curl_exec($ch);
        $err = curl_error($ch) and $out = $err;
        if (! $err) {
        	self::parseOutput($out);
        }
        unset($err);
        return $out;
    }

    /**
     * Parse exec output.
     *
     * @param string &$out
     */
    private static function parseOutput(&$out)
    {
        $a = explode("<div dir=\"ltr\" class=\"t0\">", $out, 2);
        $a = explode("<", $a[1]);
        $b = explode("<div dir=\"ltr\" class=\"o1\">", $out, 2);
        if (isset($b[1])) {
            $b = explode("<", $b[1]);
        }
        $out = html_entity_decode($a[0], ENT_QUOTES, 'UTF-8') xor
        (isset($b[1]) and $out .= "\n(".html_entity_decode($b[0], ENT_QUOTES, 'UTF-8').")");
    }
}


/**
 * Init data
 */
function init()
{
	define("data", __DIR__."/data");
	is_dir(data) or mkdir(data);
}