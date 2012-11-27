# Installation & Usage

- get [composer](http://getcomposer.org/download/),
- execute `php composer.phar install`,
- execute `php generator.php`.

# Potential issues

Crawling from the docs, some abstract classes could not be detected in a class hierarchy when the do not declare any
method or property.

You can fix this by modifying the method `Tree::fixAbstractParents()` by adding the abstract parent of a class, ie:

```php
<?php
    $parents = array(
      'KmlMultiGeometry'    => 'KmlGeometry',
      'KmlAltitudeGeometry' => 'KmlGeometry',
    );
```

The above example shows how to delcare that the abstract class `KmlGeometry` is the parent of both `KmlMultiGeometry` and
`KmlAltitudeGeometry`.

Note all classes have been handled, PRs are welcome.