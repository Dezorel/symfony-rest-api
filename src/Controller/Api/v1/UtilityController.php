<?php

namespace App\Controller\Api\v1;
use App\Enums\ResponseCode;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class UtilityController
{
    /**
     * @throws Exception
     */
    public static function validateParam(ValidatorInterface $validator, array $params, Assert\Collection $constraint): bool
    {
        $errors = $validator->validate($params, $constraint);
        
        if (isset($errors[0]))
        {
            throw new Exception($errors[0]->getMessage());
        }

        return true;
    }
}