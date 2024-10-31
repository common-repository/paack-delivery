<?php
  class PaackDeliveryUtils  {
    public static function get_utc_date(){
      $date = new DateTime();
      $date->setTimezone(new DateTimeZone("UTC"));

      return $date;
    }

    public static function format_date($date){
        $date_format = $date->format("Y-m-d H:i:s");
        $date_format = str_replace(' ','T',$date_format).".000Z";
        return $date_format;
    }
  }

?>
