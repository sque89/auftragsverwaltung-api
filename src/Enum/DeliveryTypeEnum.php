<?php
namespace App\Enum;

abstract class DeliveryTypeEnum {
    const Anlieferung = 0;
    const Abholung = 1;
    const Versand = 2;
    const Montage = 3;
    const Notizen = 4;
    const Keine = 5;
}