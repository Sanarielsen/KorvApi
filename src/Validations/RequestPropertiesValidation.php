<?php

namespace App\Validations;

class RequestPropertiesValidation
{
    public function isBothHasTheSameProperties(mixed $sendObject, mixed $jsonObject, array $excludeThatProperties): bool
    {
        $rfSendObject = new \ReflectionClass($sendObject);

        $propertyRefer = 0;
        $excludedProperty = "";

        $pnSendObject = [];
        foreach ($rfSendObject->getProperties() as $property) {
            if ( $propertyRefer <= count($excludeThatProperties) - 1 ) {
                $excludedProperty = $this->disregardThatProperty($excludeThatProperties, $excludeThatProperties[$propertyRefer]);
            }
            if ($property->getName() == $excludedProperty) {
                $propertyRefer++;
                continue;
            }
            $pnSendObject[] = $property->getName();
        }

        $propertiesJSON = [];
        foreach ($jsonObject as $key => $value) {
            $propertiesJSON[] = $key;
        }

        if ($pnSendObject === $propertiesJSON) {
            return true;
        }
        return false;
    }

    public function disregardThatProperty(array $nameProperties, string $currentProperty): string
    {
        if (in_array($currentProperty, $nameProperties) ) {
            return $currentProperty;
        }
        return "";
    }
}