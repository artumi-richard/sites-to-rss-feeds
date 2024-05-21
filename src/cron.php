<?php

//This file is meant to be run once per hour.
function go(){
    if(date('H')==9)
    {
        doDaily();
    }

}
function doDaily()
{
    include_once(__DIR__.'/SethGodin.php');
    SethGodin::createFeed();
    include_once(__DIR__.'/SheffNews.php');
    SheffNews::createFeed();

}
go();
