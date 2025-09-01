# General Notes
The following are general notes to help configure and use the application.

## Timezone handling

Automated handling of timezone between the following considerations:

- Database timezone (should be UTC)
- Default timezone if none specified
- Application timezone
- User timezone

Automated conversions is achieved by the following:

```php
//FILE: \Model\Table\AppTable.php
public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options)
{
    $typeMap = $this->getSchema()->typeMap();

    //auto conversion of DT strings to DT object using the local timezone
    if (defined('LCL_TZ') && strtolower(LCL_TZ) !== 'utc') {
        $dateCols = [];
        foreach ($typeMap as $colName => $colType) {
            if ($colType === 'datetime') {
                if ($colName !== 'created' && $colName !== 'modified') {
                    $dateCols[] = $colName;
                }
            }
        }
        foreach ($dateCols as $dateCol) {
            if (isset($data[$dateCol])) {
                if (is_string($data[$dateCol])) {
                    $data[$dateCol] = (new DateTime($data[$dateCol], LCL_TZ))->setTimezone('utc');
                }
            }
        }
    }

}
```

```
//FILE: \View\AppView.php
$this->loadHelper('Time', ['className' => 'ExtendedTime', 'outputTimezone' => LCL_TZ,]);
```

## Private and Public Pages

There are 2 controllers that serve static pages. Configuration in ```routes.php``` as follows:

```php
 /*
 * Route for Pages static pages (public facing)
 */
$builder->connect('/pages/*', 'Pages::display');

/*
 * Route for Contents static pages (internal only i.e. Auth required)
 */
$builder->connect('/contents/*', 'Contents::display');
```
