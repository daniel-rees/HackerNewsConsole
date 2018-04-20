<?php

/**
 * REALLY QUICK POC for a console news reader
 * requires php
 *
 * `php news.php -l top`
 * `php news.php -l new`
 *
 * https://github.com/HackerNews/API
 *
 *
 */

if (php_sapi_name() != 'cli') die("CLI Only");
$news = new BashNews();
$news->run();



class BashNews
{
  /**
   * [$ch description]
   * @var [type]
   */
  private $ch;


  /**
   * [$type description]
   * @var string
   */
  private $type = 'list';


  /**
   * [$url description]
   * @var array
   */
  private $url = [];


  /**
   * [$options description]
   * @var array
   */
  private $options = [];


  /**
   * [$domain description]
   * @var string
   */
  private $domain = "https://hacker-news.firebaseio.com/v0/";


  /**
   * [$suffix description]
   * @var string
   */
  private $suffix = "?print=pretty";


  /**
   * [$mapping description]
   * @var array
   */
  private $mapping = ['title', 'url', 'type'];


  /**
   * [$responseList description]
   * @var array
   */
  private $responseList = [];


  /**
   * [$limit description]
   * @var integer
   */
  private $limit = 30;


  /**
   * [$endpoints description]
   * @var array
   */
  private $endpoints = [
    'new' => 'newstories.json',
    'top' => 'topstories.json',
    'article' => 'item/%d.json',
  ];


  /**
   * [__construct description]
   */
  public function __construct()
  {
    $this->handleParams();
  }


  /**
   * [run description]
   * @return [type] [description]
   */
  public function run()
  {
    //
    echo $this->get($this->url);
  }


  private function handleParams()
  {
    $this->options = getopt("a:l:");
    $key = (!empty($this->options['l']) && array_key_exists($this->options['l'], $this->endpoints)) ? $this->options['l'] : 'new';
    $key = (!empty($this->options['a'])) ? $this->options['a'] : $key;
    $this->url = $this->domain . $this->endpoints[$key] . $this->suffix;

    $this->type = 'list';
    if('article' == $key){
      $this->type = 'article';
      $this->url = $this->getArticleUrl($this->options['a']);
    }
  }


  /**
   * [get description]
   * @param  string $url [description]
   * @return [type]      [description]
   */
  private function get($url = '')
  {
    $this->ch = curl_init();
    curl_setopt($this->ch, CURLOPT_URL, $url);
    curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($this->ch, CURLOPT_TIMEOUT, 2);

    $output = curl_exec($this->ch);
    curl_close($this->ch);


    return $this->parsed($output);
  }


  /**
   * [getUrl description]
   * @param  [type] $id [description]
   * @return [type]     [description]
   */
  private function getArticleUrl($id)
  {
    return sprintf($this->domain . $this->endpoints['article'] . $this->suffix, $id);
  }


  /**
   * [parsed description]
   * @var [type]
   */
  private function parsed($in)
  {
    $resp = json_decode($in);
    if(gettype($resp) == "array" && $this->type == "list"){
      foreach($resp as $id){
        sleep(2);
        echo $this->get($this->getArticleUrl($id));
      }

    } elseif(gettype($resp) == "object" && $resp->type == "story"){
      // die($in);
      $url = (!empty($resp->url)) ? $resp->url : '';
      return "[" . $resp->id . "] " . $resp->title . PHP_EOL .
             "[" . $resp->id . "]     " . $url .")" . PHP_EOL;
    }

    return false;
  }
}
