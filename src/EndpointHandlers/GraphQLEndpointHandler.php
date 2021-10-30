<?php

declare(strict_types=1);

namespace GraphQLByPoP\GraphQLEndpointForWP\EndpointHandlers;

use GraphQLByPoP\GraphQLEndpointForWP\ComponentConfiguration;
use PoP\API\Response\Schemes as APISchemes;
use PoP\APIEndpointsForWP\EndpointHandlers\AbstractEndpointHandler;
use PoP\ComponentModel\Constants\Params;
use PoP\ComponentModel\Services\BasicServiceTrait;
use PoP\GraphQLAPI\Component;
use PoP\GraphQLAPI\DataStructureFormatters\GraphQLDataStructureFormatter;
use Symfony\Contracts\Service\Attribute\Required;

class GraphQLEndpointHandler extends AbstractEndpointHandler
{
    use BasicServiceTrait;

    private ?GraphQLDataStructureFormatter $graphQLDataStructureFormatter = null;

    final public function setGraphQLDataStructureFormatter(GraphQLDataStructureFormatter $graphQLDataStructureFormatter): void
    {
        $this->graphQLDataStructureFormatter = $graphQLDataStructureFormatter;
    }
    final protected function getGraphQLDataStructureFormatter(): GraphQLDataStructureFormatter
    {
        return $this->graphQLDataStructureFormatter ??= $this->instanceManager->getInstance(GraphQLDataStructureFormatter::class);
    }
    /**
     * Initialize the endpoints
     */
    public function initialize(): void
    {
        if ($this->isGraphQLAPIEnabled()) {
            parent::initialize();
        }
    }

    /**
     * Provide the endpoint
     *
     * @var string
     */
    protected function getEndpoint(): string
    {
        return ComponentConfiguration::getGraphQLAPIEndpoint();
    }

    /**
     * Check if GrahQL has been enabled
     */
    protected function isGraphQLAPIEnabled(): bool
    {
        return
            class_exists(Component::class)
            && Component::isEnabled()
            && !ComponentConfiguration::isGraphQLAPIEndpointDisabled();
    }

    /**
     * Indicate this is a GraphQL request
     */
    protected function executeEndpoint(): void
    {
        // Set the params on the request, to emulate that they were added by the user
        $_REQUEST[Params::SCHEME] = APISchemes::API;
        // Include qualified namespace here (instead of `use`) since we do didn't know if component is installed
        $_REQUEST[Params::DATASTRUCTURE] = $this->getGraphQLDataStructureFormatter()->getName();
        // Enable hooks
        \do_action('EndpointHandler:setDoingGraphQL');
    }
}
