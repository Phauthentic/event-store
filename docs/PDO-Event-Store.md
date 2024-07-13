# PDO Event Store

## Using the PDO Store with ORMs

### Doctrine

```php
use Doctrine\ORM\EntityManagerInterface;

// Assuming $entityManager is your EntityManager instance
$connection = $entityManager->getConnection();
$pdo = $connection->getWrappedConnection();
```

### Laravel

```php
use Illuminate\Support\Facades\DB;

$pdo = DB::connection()->getPdo();
```

### CakePHP

```php
use Cake\Datasource\ConnectionManager;

$connection = ConnectionManager::get('default');
$pdo = $connection->getDriver()->getConnection();
```
