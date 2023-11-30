# Bundle Configuration
You can configure this bundle by creating a file under `config/packages/araise_search.yaml` that looks like this:
```yaml
# config/packages/araise_search.yaml
araise_search:
```

## Configuration Options
Under the `araise_search` key you can use any of the following options:

### `asterisk_search_enabled`

| Type    | Default | Description                                                                     |
|---------|---------|---------------------------------------------------------------------------------|
| Boolean | `false` | Is used to decide if an asterisk (`*`) should be used as a wildcard in queries. |
