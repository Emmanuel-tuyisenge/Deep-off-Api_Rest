<?php


namespace App\Serializer;

use App\Entity\Post;
use App\Security\Voter\PostVoter;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class PostApiNormalizer implements
    ContextAwareNormalizerInterface,
    NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED_NORMALIZER = 'postApiNormalizerAlreadyCalled';

    public function __construct(private AuthorizationCheckerInterface $authorizationChecker)
    {
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        $alreadyCalled = $context[self::ALREADY_CALLED_NORMALIZER] ?? false;
        return $data instanceof Post && $alreadyCalled == false;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED_NORMALIZER] = true;
        if (
            $this->authorizationChecker->isGranted(PostVoter::CAN_EDIT, $object) &&
            isset($context['groups'])
        ) {
            $context['groups'][] = 'read:collection:User';
        }

        return $this->normalizer->normalize($object, $format, $context);
    }
}
