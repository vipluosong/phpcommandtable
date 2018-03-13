<?php
/**
 * Created by PhpStorm.
 * User: luosong
 * Date: 2017/9/4
 * Time: 上午10:56
 */

class table
{
  public $lines = 1;//行数
  public $columns = 1;//列数
  public $columns_with = [];//列宽

  public $chinese_rule = "/^[\x{4e00}-\x{9fa5}]+$/u";//全汉字
  public $english_rule = "/^[\d\w]+$/";//全英文

  public $align = 'left';//right 左对齐  右对齐
  public $br = true;//是否换行

  public $title = '';//表title
  public $body = '';//表list
  public $sumcharacter = '+';//"+"字符
  public $vertical = '|';//表格的竖线
  public $horizontal = '-';//表格的竖线

  const UNIT = 2;//定义横线比率

  public function __construct()
  {
  }

  public function showTable(array $data,$align = 'left',$br=true)
  {
    array_key_exists('title',$data) && $this->title = $data['title'];
    array_key_exists('body',$data) && $this->body = $data['body'];

    $this->columns_with();
    $columns_with = $this->columns_with;
    $columns = $this->columns;
    $this->echo_table_title();

    $this->columns_with = $columns_with;
    $this->columns = $columns;
    $this->br = $br;
    $this->align = $align;
    $this->echo_table_tr($data['body']);

  }

  //输出表格title
  public function echo_table_title(){
    $this->br = false;
    $this->columns_with = [array_sum($this->columns_with)+($this->columns-1)/2];
    $this->columns = 1;
    $this->align = 'center';
    $data = [$this->title];
    $this->echo_table_tr($data);
  }

  //输出单元格
  public function echo_table_tr(array $body)
  {
    foreach ($body as $key => $value) {
      $horizontals = '';
      $verticalStr = '';
      for ($i = 0; $i < $this->columns; $i++) {
        $horizontals .= $this->sumcharacter . $this->horizontals($this->columns_with[$i]);//+---

        if (array_key_exists($i, $value) && !empty($value[$i])) {
          $str = $this->str_align($value[$i], $this->columns_with[$i]);
        } else {
          $str = $this->echoBlankSpace($this->columns_with[$i] * self::UNIT);
        }

        $verticalStr .= $this->vertical . $str;//|str
      }

      echo $horizontals . $this->sumcharacter . PHP_EOL;//+---+
      echo $verticalStr . $this->vertical . PHP_EOL;//|str|
    }
    if($this->br){
      echo $horizontals . $this->vertical . PHP_EOL;//+---+
    }
  }

  //定义每列的宽度和行数
  public function columns_with(){
    foreach ($this->body as $key => $value) {
      $this->lines < count($value) && $this->lines = count($value);//定义行数

      if (is_array($value)) {
        foreach ($value as $k => $val) {//定义每列的最小宽度
          if (array_key_exists($k, $this->columns_with)) {
            $this->columns_with[$k] < mb_strlen($val) && $this->columns_with[$k] = mb_strlen($val);
          } else {
            $this->columns_with[$k] = mb_strlen($val);
          }
        }
      } else {
        $this->columns_with[$key] = mb_strlen($value);
      }
    }

    $this->columns = count($this->columns_with);
  }

  //表格的横线
  public function horizontals($with = '')
  {
    $line_2D = '';
    for ($i = 0; $i < $with * self::UNIT; $i++) {
      $line_2D .= $this->horizontal;
    }

    return $line_2D;
  }

  //返回补位空格
  public function echoBlankSpace($num = 1)
  {
    $blankSpace = '';
    if ($num >= 1) {
      for ($i = 0; $i < $num; $i++) {
        $blankSpace .= ' ';
      }
    }

    return $blankSpace;
  }

  //定义在限定列宽中不足列宽的用空格补齐
  public function str_align($str = '', $strlen = 1)
  {//列宽
    empty($str) && die('参数的值不能为空' . PHP_EOL);
    if (preg_match_all($this->english_rule, $str, $match)) {//全是英文字符或数字
      $blankSpace = $this->english_str($str, $strlen);
    } elseif (preg_match_all($this->chinese_rule, $str, $match)) {//全部是汉字
      $blankSpace = $this->chinese_str($str, $strlen);
    } else {//英文字符或汉字
      if (preg_match_all("/([\dA-Za-z]+)/", $str, $match)) {
        $blankSpace = $this->english_chinese_str($str, $match, $strlen);
      } else {
        die('匹配规则异常！' . PHP_EOL);
      }
    }

    switch ($this->align) {
      case 'right'://左对齐
        $str = $blankSpace . $str;
        break;

      case 'center'://居中对齐
        $str = $this->center_align($str, mb_strlen($blankSpace));
        break;

      default:
        $str .= $blankSpace;
        break;
    }

    return $str;
  }

  //剧中对齐
  public function center_align($str = '', $strlen = 0)
  {
    $left = $this->echoBlankSpace(floor($strlen / 2));
    $right = $this->echoBlankSpace($strlen - floor($strlen / 2));

    return $left . $str . $right;
  }

  //全中文字符处理
  public function chinese_str($str, $strlen)
  {
    $blankSpace = $this->echoBlankSpace(($strlen - mb_strlen($str)) * self::UNIT);

    return $blankSpace;
  }

  //全英文数字字符处理
  public function english_str($str, $strlen)
  {
    $num = ($strlen * self::UNIT - strlen($str));
    $blankSpace = $this->echoBlankSpace($num);

    return $blankSpace;
  }

  //中英文字符处理
  public function english_chinese_str($str, $match, $strlen)
  {
    $strbk = implode('', $match[0]);
    $num = $strlen * self::UNIT - (strlen($strbk) + (mb_strlen($str) - strlen($strbk)) * self::UNIT);

    return $this->echoBlankSpace($num);
  }


  //接收参数
  public function selectOb()
  {

    $fs = true;

    do {
      if ($fs) {
        fwrite(STDOUT, '请输入您要查询的项目编号：');
        $fs = false;
      } else {
        fwrite(STDOUT, '抱歉，项目编号不能为空，请重新输入项目编号：');
      }

      $name = trim(fgets(STDIN));

    } while (!$name);

    if(array_key_exists($name, $this->body)){
      $list['title'] = $this->title;
      $list['body'] = [$this->body[$name]];
      $this->showTable($list);
    }else{
      die('您输入的数据非法！' . PHP_EOL);
    }

  }
}


$list['title'] = ['日志系统'];
//$list['body'] = [
//  ['编号', '项目名称', '项目别名'],
//  ['1', '好贷宝', 'hdb'],
//  ['2', '钱包金融', 'qbjr'],
//  ['3', '廊坊银行', 'lccb']
//];
//$list['body'] = [['编号','项目名称','简称'],['称','好贷宝','hdb'],['称','钱包金融','qbjr'],['称','廊坊银行','lccb']];
//$list['body'] = [['编号','项目名称','简称'],['称a','好贷宝',''],['称','钱包金融',''],['年','廊坊银行','']];
//$list['body'] = [['好贷宝123456', '好贷宝'], ['贷宝11', '廊坊银行行'], ['银2', '钱包金融'], ['支', '支付宝'], ['a'], ['1', '2222']];
$list['body'] = [['好', '好贷宝'], ['123213', '好贷宝'], ['12s', '廊坊银行'], ['2', '钱包金融'], ['3', '支付宝'], ['4']];
//$list['body'] = [['a', 'b'], ['1', 'q'], ['2', 'w'], ['3', 'e'], ['4']];
//$list['body'] = [['不支持asdasd']];

$table = new table();
$table->showTable($list);
$table->selectOb();