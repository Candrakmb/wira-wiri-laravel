<?php

function rupiah($number)
{
    return "Rp " . number_format($number,0,',','.');
}

function waktu($waktu)
{
    return \Carbon\Carbon::parse($waktu)->translatedFormat('H:i');
}

function tanggal($tanggal){
    // Set locale ke bahasa Indonesia
    \Carbon\Carbon::setLocale('id');
    // Format tanggal
    return \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y');
}

function statusOrder($status){
    
}
