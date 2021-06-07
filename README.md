# Vdm Library Doctrine Transport

## Installation

```bash
composer require 3slab/vdm-library-doctrine-transport-bundle
```

You need to have either (or both) doctrine ORM or ODM installed

```bash
composer require symfony/orm-pack
```

Or

```bash
composer require doctrine/mongodb-odm-bundle
```

## Configuration reference

There are two parts ton configure: the transport, and Doctrine's behaviour.

### Transport

In `messenger.yaml`:

```yaml
framework:
    messenger:
        transports:
            producer:
                dsn: vdm+doctrine_orm://mycustomconnection
                options:
                    doctrine_executor: ~
                    default_entity: ~
                    entities:
                        App\Entity\Demande:
                            selector: RefDemande
```

Configuration | Description
--- | ---
dsn | Use `vdm+doctrine_orm://` (if entity manager) or `vdm+doctrine_odm://` (if document manager). Optionnaly, you can specify the connection to use with `vdm+doctrine_orm://mycustomconnection` (fits into `doctrine.orm.xxx_entity_manager`).
options.doctrine_executor | set the id (in the container of services) of a custom doctrine executor to use instead of the [DefaultDoctrineExecutor](./Executor/DefaultDoctrineExecutor.php)
options.default_entity | set the class of default entity to populate if none passed in the message metadatas
options.entities | Array of entities to register. At least one entity must be declared.
options.entities.FQCN.selector | (optional) Define how the executor will try and fetch a pre-existing entity before persisting (see below)

## Fetching pre-existing entity

Before persisting anything, this transport will always try to find an existing entity. You need to tell it how to 
proceed. You have several ways of doing it.

### The natural way

It means that your entity bears a unique identifier value, such as:
```php
    /**
     * @ORM\Id()
     */
    private $id;
```

If this value is carried by the incoming message, then you have nothing to configure. The only responsability on 
your end is making sure there is a public getter for this property (if there isn't you'll get a clear error message 
anyway).

__Note__: in this case, the sender will use the  `find` method on the repository.

### Multifield with natural getters

In case you don't have a mono-column primary key (ex: no key at all or composite key), you can turn to another 
approach and tell the executor which fields should be used to retrieve a pre-existing entity. For instance, if 
your entity has two fields representing its identity (let's say `code` and `hash`), and they both have a natural 
getter (i.e. `getCode` and `getHash`), then you need to configure the options like this:

```yaml
framework:
    messenger:
        transports:
            producer:
                dsn: vdm+doctrine://
                options:
                    entities:
                        App\Entity\Demande:
                            selector:
                                - code
                                - hash
```

Under the hood, the repository will be called like:

```php
$repo->findOneBy([ 'code' => $yourEntity->getCode(), 'hash' => $yourEntity->getHash() ])
```

__Note__: Notice the `findOneBy`. The sender will use the first matching entity. It's your responsability to provide 
a unique set of filter.

### Multifield with non-natural getters

In case the fields related to the identity have unnatural getters (ex: legacy code, multilingual code), you can 
define which getter to use to fetch the appropriate property. Let's say the identity is made of two fields: `label` 
and `hash`, which respective getters are `getLibelle()` and `hash()`. You will need configure the sender as such:

```yaml
framework:
    messenger:
        transports:
            producer:
                dsn: vdm+doctrine://
                options:
                    entities:
                        App\Entity\Demande:
                            selector:
                                label: getLibelle
                                hash: hash
```

Under the hood, the repository will be called like:

```php
    $repo->findOneBy([ 'label' => $yourEntity->getLibelle(), 'hash' => $yourEntity->hash() ])
```

The same policy as natural getters apply: you have to make sure it returns something as unique as possible.

You can define several entities at once, and mix natural and non-natural getters. However, you will have to 
prefix your natural getters with integer keys. The key itself doesn't matter (as long as you don't create duplicates), 
it just needs to be an integer. If the key is an integer, the getter will be guessed. Otherwise, the getter will be 
what you provide

```yaml
framework:
    messenger:
        transports:
            producer:
                dsn: vdm+doctrine://
                options:
                    entities:
                        App\Entity\Foo:
                            selector:
                                0: code # hack to mix natural and non-natural getters
                                label: getLibelle #non natural getter
                                hash: hash #non natural getter
                        App\Entity\Bar: ~ # Bar has a single-field identity (id) with natural getter, no configuration needed
                        App\Entity\Baz:
                            selector:
                                - reference # Baz uses a filter based on its reference with natural getter (getReference)
```

Under the hood, the repository will fetch the entities like this:
```php
// Foo
$repo->findOneBy([ 'code' => $foo->getCode(), 'label' => $foo->getLibelle(), 'hash' => $foo->hash() ]);

// Bar
$repo->find($bar->getId());

// Baz
$repo->findOneBy([ 'reference' => $baz->getReference() ]);
```

## Doctrine Executor

Doctrine executor allows you to customize the behavior of the doctrine ORM transport per transport definition 
inside your `messenger.yaml` file.

If you don't set a custom `doctrine_executor` option when declaring the transport, the default 
[DefaultDoctrineExecutor](./Executor/DefaultDoctrineExecutor.php) is used.

You can override this behavior in your project by providing a class that extends 
`Vdm\Bundle\LibraryDoctrineTransportBundle\Executor\AbstractDoctrineExecutor`.

```php
namespace App\Executor\Doctrine;

use Vdm\Bundle\LibraryDoctrineTransportBundle\Executor\AbstractDoctrineExecutor;
use Vdm\Bundle\LibraryBundle\Model\Message;

class CustomDoctrineExecutor extends AbstractDoctrineExecutor
{
    public function execute(Message $message): void
    {
        if (!$this->manager) {
            throw new NoConnectionException('No connection was defined.');
        }

        $entityMetadatas = $message->getMetadatasByKey('entity');
        $entityMetadata  = array_shift($entityMetadatas);
        $entityClass     = $entityMetadata->getValue();
        $entity          = $this->serializer->denormalize($message->getPayload(), $entityClass);
        $entity          = $this->matchEntity($entity);

        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
```

Then references this custom executor in your transport definition in your project `messenger.yaml` :

```yaml
framework:
    messenger:
        transports:
            store-entity:
                options:
                    doctrine_executor: App\Executor\Doctrine\CustomDoctrineExecutor
```

## Entity/Document Matching

For the transport to know to which entities or documents the payload should be persisted, you can either :

* provide the entity's fully qualified class name in the message's metadata, with key `entity`. Example `new Metadata('entity', 'App\Entity\Foo')`.
* configure a default entity on the transport level

```yaml
framework:
    messenger:
        transports:
            store-entity:
                options:
                    default_entity: App\Entity\Foo
```

## Limitations

You cannot use different connections for different entities within one single transport. Should you have such a need, 
you should define one transport per connection, extends the library's Message (one per producer) and route the correct 
message to the correct producer.