<?php return array (
  'providers' => 
  array (
    0 => 'Modules\\Calls\\Providers\\CallsServiceProvider',
    1 => 'Modules\\Calls\\Providers\\VoIPServiceProvider',
  ),
  'eager' => 
  array (
    0 => 'Modules\\Calls\\Providers\\CallsServiceProvider',
  ),
  'deferred' => 
  array (
    'Modules\\Calls\\VoIP\\Contracts\\VoIPClient' => 'Modules\\Calls\\Providers\\VoIPServiceProvider',
  ),
  'when' => 
  array (
    'Modules\\Calls\\Providers\\VoIPServiceProvider' => 
    array (
    ),
  ),
);