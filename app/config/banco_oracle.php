<?php
return [
    'type'    => 'oracle',
    'driver'  => 'oci',
    'tns'     => "(DESCRIPTION=
                    (ADDRESS=(PROTOCOL=TCP)(HOST=127.0.0.1)(PORT=1525))
                    (CONNECT_DATA=(SERVICE_NAME=NOME.SID))
                  )",
    'user'    => 'usuario',
    'pass'    => 'senha',
    'charset' => 'AL32UTF8',
];
