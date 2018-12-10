<?php

namespace Garlic\Bus\Service\Processor;

use Enqueue\Client\TopicSubscriberInterface;
use Garlic\Bus\Service\Abstracts\ProcessorConfigAbstract;
use Garlic\Bus\Service\CommunicatorService;
use Interop\Queue\PsrProcessor;
use Interop\Queue\PsrContext;
use Interop\Queue\PsrMessage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ServiceDiscoveryProcessor extends ProcessorConfigAbstract implements PsrProcessor, TopicSubscriberInterface
{
    public static function getSubscribedTopics()
    {
        return ['serviceDiscovery']; //, 'anotherTopic' and any other
    }

    /**
     * {@inheritdoc}
     * @todo:: implement
     */
    public function process(PsrMessage $message, PsrContext $context)
    {
        $container = $this->kernel->getContainer();

        $response = $container->get('http_kernel')
            ->handle(Request::create('/graphql', 'POST', ['query' => $this->getIntrospectionQuery()]), HttpKernelInterface::MASTER_REQUEST);

        $container->get(CommunicatorService::class)
            ->command('gateway')
            ->post()
            ->serviceRebuildSchema(['data' => $response->getContent()]);

        return self::ACK;
    }

    protected function getIntrospectionQuery()
    {
        return '
        query IntrospectionQuery {
    __schema {
      queryType { name }
      mutationType { name }
      subscriptionType { name }
      types {
        ...FullType
      }
      directives {
        name
        description
        args {
          ...InputValue
        }
        onOperation
        onFragment
        onField
      }
    }
  }
  
    fragment FullType on __Type {
    kind
    name
    description
    fields(includeDeprecated: true) {
      name
      description
      args {
        ...InputValue
      }
      type {
        ...TypeRef
      }
      isDeprecated
      deprecationReason
    }
    inputFields {
      ...InputValue
    }
    interfaces {
      ...TypeRef
    }
    enumValues(includeDeprecated: true) {
      name
      description
      isDeprecated
      deprecationReason
    }
    possibleTypes {
      ...TypeRef
    }
  }

  fragment InputValue on __InputValue {
    name
    description
    type { ...TypeRef }
    defaultValue
  }

  fragment TypeRef on __Type {
    kind
    name
    ofType {
      kind
      name
      ofType {
        kind
        name
        ofType {
          kind
          name
        }
      }
    }
}
        ';
    }
}
