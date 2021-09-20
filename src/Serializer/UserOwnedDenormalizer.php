<?php


namespace App\Serializer;


use App\Entity\UserOwnedInterface;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\BadMethodCallException;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;

class UserOwnedDenormalizer implements
    ContextAwareDenormalizerInterface,
    DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED_DENORMALIZER = 'UserOwnedDenormalizerCalled';

    public function __construct(private Security $security, private UserRepository $repository)
    {
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        $reflectionClass = new \ReflectionClass($type);
        $alreadyCalled = $context[$this->getAlreadyCalledKey($type)] ?? false;
        return $reflectionClass->implementsInterface(UserOwnedInterface::class) && $alreadyCalled == false;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {

        $context[$this->getAlreadyCalledKey($type)] = true;
        /**@var UserOwnedInterface $obj */
        $obj = $this->denormalizer->denormalize($data, $type, $format, $context);
        $user = $this->repository->find($this->security->getUser()->getId());
        $obj->setUser($user);
        #$obj->setUser($this->security->getUser());
        //dd($obj);
        return $obj;
    }

    private function getAlreadyCalledKey(string $type)
    {
        return self::ALREADY_CALLED_DENORMALIZER . $type;
    }
}
