@props([
    'data' => [],
    'header' => [],
    'menu' => [],
    'submenu' => [],
])
<x-core::layouts.list :$data :$header :$menu :$submenu />