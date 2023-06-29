# Configuration

There are two ways to configure the indexed entities. Either by using annotations or by using the `config.yml` file of your symfony application. It is also possible to mix both variants.

## Annotations

In your entities, you have to configure the indexed fields with the index annotation:

```
// src/Entity/User.php

// ...

use Doctrine\ORM\Mapping as ORM;
use araise\SearchBundle\Annotation\Index;

// ...

    #[ORM\Column(type: 'text', name='firstname')]
    #[Index]
    protected string $firstname;
    
// ...
```

It is possible to define a custom formatter:

```
// src/Entity/User.php

// ...

use Doctrine\ORM\Mapping as ORM;
use araise\SearchBundle\Annotation\Index;

// ...

    #[ORM\Column(type: Types::DATETIME_MUTABLE, name="created_at")]
    #[Index(formatter="araise\CoreBundle\Formatter\DateTimeFormatter")]
    protected DateTimeInterface $createdAt;
    
// ...
```

You can index a method return value too:

```
// src/Entity/User.php

// ...

use Doctrine\ORM\Mapping as ORM;
use araise\SearchBundle\Annotation\Index;

// ...

    #[Index]
    public function getFullname(): string
    {
        return $this->firstname.' '.$this->lastname;
    }
    
// ...
```

Annotations for modifing the search results, with preSearch and postSearch hooks

```
// src/Entity/User.php

// ...

use Doctrine\ORM\Mapping as ORM;
use araise\SearchBundle\Annotation\Index;
use araise\SearchBundle\Annotation\Searchable;

// ...
/**
 * Personen.
 */
 #[ORM\Table(name="users")]
 #[Searchable([preSearch="App\Agency\Search\UserPreSearch", preSearch="App\Agency\Search\UserPostSearch"])]
class User
{
   // .....
    

```

## Index groups

You can define indexing groups and restrict search within these. If not specified the standard group is ```default```

```
#[Index groups: ['default', 'posts']]
private $title;

#[Index]
private $description;
```

In the controller set which group(s) you want to include using ```SearchOptions::OPTION_GROUPS```

```
$searchParams = $this->getGlobalResults($request, $searchManager, [
    SearchOptions::OPTION_GROUPS => [
        'posts'
    ],
]);
```


The preSearch Hook 
```
// src/Search/UserPreSearch.php

// ...

use Doctrine\ORM\QueryBuilder;
use araise\SearchBundle\Entity\PreSearchInterface;

// ...
class UserPreSearch implements PreSearchInterface
{
   // .....
    public function preSearch(QueryBuilder &$qb, string $query, ? string $entity, ? string $field): void
    {
        // modify the QueryBuilder
    }    

```


The postSearch Hook 
```
// src/Search/UserPostSearch.php

use araise\SearchBundle\Entity\PostSearchInterface;

class PersonPostSearch implements PostSearchInterface
{


    public function postSearch(array $queryResults, string $query, ?string $entity, ?string $field): array
    {

        // modify queryResults
        $modifiedResults = [];

        foreach ($queryResults as $queryResult) {
            // remove special Entity 1
            if ($queryResult['foreignId'] == 1) {
                continue;
            }

            // remvoe low matchQuotes
            if ($queryResult['_matchQuote'] < 15) {
                continue;
            }

            $modifiedResults[] = $queryResult;
        }

        return $modifiedResults;
    }

}

```




## Configuration file

It's also possible to configure the indexed fields in your `config.yml` 
or create `config/packages/araise_search.yaml` for Symfony 4

```
araise_search:
    entities:
        user:
            class: Agency\UserBundle\Entity\User
            fields:
                - { name: firstname }
                - { name: createdAt, formatter: araise\CoreBundle\Formatter\DateTimeFormatter }
```

