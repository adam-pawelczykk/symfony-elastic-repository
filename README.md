# Elastic
Bundle daje możliwość integracji z elastic search na zasadzie repozytoriów indexu.

## Instalacja

```
composer require atpawelczyk/elastic
```

dodaj bundle do config/bundles.php

```
ATPawelczyk\Elastic\ElasticBundle::class => ['all' => true]
```

## Integracja z symfony yaml
Poniżej przykładowy plik konfiguracyjny. 
Musimy zadbać o dostarczenie klienta elastica (o tym na samym końcu).

```yaml
gd_elastic:
    client: 'Elasticsearch\Client'
    prefix: '%kernel.environment%'
    bus: 'messenger.bus.elastic'
    indexes:
        stock: User\ReadModel
# Or more advanced
        stock:
            class: User\ReadModel
            prefix: 'individual' # nadpisuje główny prefix 
            settings:
                analysis:
                normalizer:
                    lowercase_normalizer:
                        type: custom
                        filter: [lowercase, asciifolding]
            properties:
                userName:
                    type: keyword
                    ignore_above: 100
                    normalizer: lowercase_normalizer
```

Możemy pokusic się o wydzielenie osobnego busa do przetwarzania komend synchronizacji dokumentów elastica
a co za tym idzie ograniczenie middlewares pośredniczącym w przetworzeniu komendy.

```yaml
framework:
    messenger:
        buses:
            messenger.bus.elastic:
```

##Testy in memory
Możemy podmienić seriws managera w testach na InMemoryIndexManager
Wystarczy w services_test.yam dodać
```yaml
services:
    ATPawelczyk\Elastic\IndexManagerInterface: '@ATPawelczyk\Elastic\InMemoryIndexManager'
```

## Przykładowe użycie
### Tworzenie własnego indeksu

Najważniejszą metodą jest tutaj metoda sync która pozwala zachować format modelu 
podczas synchronizacji dokumentu z elasticiem.

```php
<?php

use ATPawelczyk\Elastic\IndexManagerInterface;

/**
 * Class UserIndex
 * @package App\Module\User\Elasticsearch
 */
class UserIndex
{
    private $index;
    private $denormalizer;

    /**
     * UserIndex constructor.
     * @param IndexManagerInterface $manager
     */
    public function __construct(IndexManagerInterface $manager, DenormalizerInterface $denormalizer)
    {
        $this->index = $manager->getIndex(User\ReadModel::class);
        $this->denormalizer = $denormalizer;
    }

    /**
     * @param User\ReadModel $model
     */
    public function sync(User\ReadModel $model): void
    {
        $this->index->sync(new Document($model->uuid, (array) $model));
    }
    
    /**
     * @param UuidInterface $uuid
     * @return User\ReadModel|null
     */
    public function get(UuidInterface $uuid): ?User\ReadModel
    {
        if (null === $data = $this->index->source($uuid->toString())) {
            return null;
        }
        /** @var User\ReadModel $model */
        $model = $this->denormalizer->denormalize($data, User\ReadModel::class);

        return $model;
    }
    
    /**
     * @param UuidInterface $uuid
     * @return array|null
     */
    public function getData(UuidInterface $uuid): ?array
    {
        return $this->index->get($uuid->toString());
    }

    /**
     * @param DSLQueryStackInterface $queryStack
     * @return mixed[]
     */
    public function search(DSLQueryStackInterface $queryStack): array
    {
        return $this->index->search($queryStack);
    }
}
```

## Implementacja message bus interface
Konfigurując bundla w oparciu o bus należy pamiętać aby dodawać new DispatchAfterCurrentBusStamp
do komendy odpowiedzialnej za synchronizacje danego dokumentu bądź do eventu który ją wywołuje
w przypadku obsługi synchronicznej. Dodanie takiego znaczka spowoduje uruchomienie komendy bądź
eventu po poprawnym przeprocesowaniu bieżącego procesu a co za tym idzie będziemy mieli commit
na zmianach danych w bazie danych.

Więcej informacji znajdziesz tutaj: https://symfony.com/doc/current/messenger/dispatch_after_current_bus.html

### Ograniczenie dostępu
Czasem zdarza się, że trzeba ograniczyć dostęp do dokumentów względem jakiegoś
pola. W przypadku pojedyńczego dokumentu możemy stworzyć votera ale w przypadku
wielu dokumentów użyjemy filtra w DSLQueryStackInterface.

Poniżej przykładowe użycie w przypadku tablicy oraz konkretnego dopasowania.
Bardzo ważną rzeczą jest dodanie słówka keyword w przypadku gdy przeszukujemy 
ciągi znaków oddzielone spacją, myślnikiem bądź innym czymś... 

Gdybyśmy wyszukiwali po id gdzie elastic nie rozbije ciągu znaków na poszczególne
tagi nie musimy dopisywać keyword.
```php
new Filter(Filter::TERM, 'user.id', 100);   
```

Przykładowy filter factory

```php
<?php

use App\Auth;
use ATPawelczyk\Elastic\DSL\DSLQueryStackInterface;
use ATPawelczyk\Elastic\DSL\FilterValue;

/**
 * Class UserFiltersFactory
 * @package App\Module\User\UI\Security
 */
class UserFiltersFactory
{
    private $auth;

    /**
     * UserFiltersFactory constructor.
     * @param Auth $auth
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }
    
    /**
     * @param DSLQueryStackInterface $dslQueryStack
     * @return DSLQueryStackInterface
     */
    public function fromDslQueryStack(DSLQueryStackInterface $dslQueryStack): DSLQueryStackInterface
    {
        $organizationFilter = new FilterValue(FilterValue::TERMS, 'organization.uuid.keyword', $this->auth->getOrganizationUuids());
        $dslQueryStack->addFilter($organizationFilter);
        
        if (! $this->auth->isImporter()) {
            $userFilter = new FilterValue(FilterValue::TERM, 'user.uuid.keyword', $this->auth->getUserUuid());
            $dslQueryStack->addFilter($userFilter);
        }

        return $dslQueryStack;
    }
}
```

### Użycie indeksu w kontrolerze
W akcji index przeszukujemy całą kolekcje dokumentów UserIndex używając 
query dostarczonych metodą post z frontendu.

W akcji user użyliśmy User\ReadModel oraz UUID (id dokumentu) a sam bundle
elastica wstrzyknie nam ten dokument wykorzystując deklaracje indeksu w yaml.
W przypadku gdy dokument nie będzie istniał dostaniesz 404 na twarz. 
```php
<?php

use ATPawelczyk\Elastic\Request;
use App\Module\User\Elasticsearch\UserIndex;
use App\Module\User\UI\Security\UserFiltersFactory;
use App\Api\ApiResponse;

/**
 * Class UserController
 * @package App\Module\User\UI\Controller
 */
class UserController
{
    /**
     * @Route("/users", methods={"POST"})
     * @param Request $request
     * @param UserIndex $manager
     * @param UserFiltersFactory $filtersFactory
     * @return ApiResponse
     */
    public function index(Request $request, UserIndex $manager, UserFiltersFactory $filtersFactory): ApiResponse
    {
        $dsl = $filtersFactory->fromDslQueryStack($request->getDSLQueryStack());
    
        return $this->response($manager->search($dsl));
    }
    
    /**
     * @Route("/user/{user<[a-z\d\-]{36}>}", methods={"GET"})
     * @param User\ReadModel $user
     * @return ApiResponse
     */
    public function user(User\ReadModel $user): ApiResponse
    {
        return $this->response($user);
    }
}
```

### Fabryka dla klienta ElasticSearch
Fabryka której celem jest stworzenie klienta Elasticsearch\Client

```php
<?php
/** @author Adam Pawełczyk */

namespace App\System\Elastic;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use InvalidArgumentException;
use Safe\Exceptions\UrlException;

/**
 * Factory to create ElasticSearch Client using environments
 * @package App\System\Elastic\DependencyInjection
 */
class ClientFactory
{
    /** @var string */
    private $host;
    /** @var string|null */
    private $username;
    /** @var string|null */
    private $password;
    /** @var string|null */
    private $scheme;
    /** @var int|null */
    private $port;

    /**
     * ClientFactory constructor.
     * @param bool $cloud
     * @param string $url
     * @param string $credentials
     * @throws UrlException
     */
    public function __construct(bool $cloud, string $url, string $credentials)
    {
        if (empty($url)) {
            throw new InvalidArgumentException('Environment of Elasticsearch URL is empty');
        }

        $credentials = explode(':', $credentials, 2);

        $this->username = $credentials[0] ?? null;
        $this->password = $credentials[1] ?? null;

        if ($cloud) {
            $this->host = $url;
        } else {
            $parsed = \Safe\parse_url($url);

            if (empty($parsed['scheme']) || empty($parsed['host'])) {
                throw new InvalidArgumentException('Environment of Elasticsearch URL is invalid, scheme or host is empty');
            }

            $this->scheme = (string) $parsed['scheme'];
            $this->host = (string) $parsed['host'];
            $this->port = (int) $parsed['port'] ?? 9200;
        }
    }

    /**
     * @return Client
     */
    public function create(): Client
    {
        $client = ClientBuilder::create();

        if (null === $this->scheme) {
            $client->setElasticCloudId($this->host);
        } else {
            $client->setHosts([
                ['host' => $this->host, 'port' => $this->port, 'scheme' => $this->scheme]
            ]);
        }

        if (! empty($this->username)) {
            $client->setBasicAuthentication($this->username, $this->password ?? '');
        }

        return $client->build();
    }
}
```

Konfiguracja yaml w celu umożliwienia autowire klienta elasica

```yaml
services:
    App\System\Elastic\ClientFactory:
        lazy: true
        arguments: ['%env(bool:ELASTICSEARCH_CLOUD)%', '%env(ELASTICSEARCH_URL)%', '%env(ELASTICSEARCH_CREDENTIALS)%']

    Elasticsearch\Client:
        lazy: true
        factory:   ['@App\System\Elastic\ClientFactory', create]
```

Konfiguracja env

```dotenv
###> ELASTIC SEARCH ###
ELASTICSEARCH_CLOUD=true/false
ELASTICSEARCH_URL=....
ELASTICSEARCH_CREDENTIALS=.....
###< ELASTIC SEARCH ###
```
