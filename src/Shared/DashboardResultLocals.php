<?php

namespace App\Shared;

class DashboardResultLocals
{
    private int $idLocal;
    private string $nameLocal;
    private int $regionId;
    private mixed $sensors = [];

    public function setIdLocal(int $idLocal): void
    {
        $this->idLocal = $idLocal;
    }

    public function getIdLocal(): int
    {

        return $this->idLocal;
    }

    public function setNameLocal(string $nameLocal): void
    {
        $this->nameLocal = $nameLocal;
    }

    public function getNameLocal(): string
    {
        return $this->nameLocal;
    }

    public function setRegionId(int $regionId)
    {

        $this->regionId = $regionId;
    }

    public function getRegionId()
    {

        return $this->regionId;
    }

    public function setSensors(mixed $sensors)
    {

        $this->sensors = $sensors;
    }

    public function getSensors()
    {

        return $this->sensors;
    }
}


