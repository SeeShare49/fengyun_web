<?php

$file_path = dirname(__FILE__) . "/chat_file.txt";
//fclose(fopen($file_path));



unlink($file_path);

//file_put_contents($file_path,'');