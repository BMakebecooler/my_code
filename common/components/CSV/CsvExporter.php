<?php
namespace common\components\CSV;

class CsvExporter {

  private $headers = [];
  private $rows = [];
  private $separator;

  function __construct($name, $separator = ';')
  {
    $this->separator = $separator;
    /*header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=".$name."-".date('d-m-Y').".csv");
    header("Pragma: no-cache");
    header("Expires: 0");*/
  }

  public function setHeader($items)
  {
    $this->headers = $items;
  }

  public function addRow($items)
  {
    $this->rows[] = $items;
  }

  public function out($descriptor = 'php://output')
  {
    $handle = fopen($descriptor, 'w');
    fputcsv($handle, $this->headers, $this->separator);
    foreach ($this->rows as $row) {
      fputcsv($handle, $row, $this->separator);
    }
    fclose($handle);
  }

}
