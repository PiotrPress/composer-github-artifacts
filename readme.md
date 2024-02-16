# Composer GitHub Artifacts

This Composer plugin adds support for using [GitHub Workflow Artifacts](https://docs.github.com/en/actions/using-workflows/storing-workflow-data-as-artifacts) as [Composer Repositories](https://getcomposer.org/doc/05-repositories.md#composer).

It also provides [GitHub Workflows](https://docs.github.com/en/actions/using-workflows) to build and update the Composer repository based on packages from GitHub repositories.

## Example `composer.json`

```json
{
  "repositories": [
    {
        "type": "composer",
        "url": "github.artifacts://PiotrPress/packages"
    }
  ]
}
```

## Installation

1. Add the plugin as a global composer requirement:

```shell
$ composer global require piotrpress/composer-github-artifacts
```

2. Allow the plugin execution:

```shell
$ composer config -g allow-plugins.piotrpress/composer-github-artifacts true
```

## Authentication

Add GitHub API [authentication](https://getcomposer.org/doc/articles/authentication-for-private-packages.md#http-basic) credentials:

```shell
$ composer config --global http-basic.github.com x-oauth-basic <token>
```

**NOTE:** using `--global` option is recommended to keep credentials outside of project's files.

Arguments:

- `token` - the [token](https://github.com/settings/tokens) must have permissions to [get repository content](https://docs.github.com/en/rest/repos/contents#get-repository-content) and [list artifacts for a repository](https://docs.github.com/en/rest/actions/artifacts#list-artifacts-for-a-repository)

## Workflows

This plugin also comes with two [Reusable Workflows](https://docs.github.com/en/actions/using-workflows/reusing-workflows) designed to build and update the packages.json artifact based on packages from GitHub repositories belonging to the provided owner.

### Build

Add a `.github/workflows/build.yml` file with the content below to the GitHub repository where the artifact containing the list of packages should be kept.

```yml
name: Build packages.json
on: 
  workflow_dispatch:
  repository_dispatch:
    types: [ Update packages.json ]
jobs:
  build:
    uses: PiotrPress/composer-github-artifacts/.github/workflows/build.yml@master
    secrets:
      token: ${{ secrets.token }}
    with:  
      owner: ${{ vars.owner }}
```

**NOTE:** by using the `workflow_dispatch` event, this workflow can also be triggered manually.

Workflow [secrets](https://docs.github.com/en/actions/security-guides/using-secrets-in-github-actions) and [variables](https://docs.github.com/en/actions/learn-github-actions/variables):

- `secrets.token` - required: `false`, the [token](https://github.com/settings/tokens) must have permissions to [get repository content](https://docs.github.com/en/rest/repos/contents#get-repository-content)
- `vars.owner` - required: `false`, default: `github.repository_owner`

### Update

Add a `.github/workflows/update.yml` file with the content below to the GitHub repository, which is configured to trigger an update to the package list after every push.

```yml
name: Update packages.json
on:
  workflow_dispatch:
  push:
jobs:
  update:
    uses: PiotrPress/composer-github-artifacts/.github/workflows/update.yml@master
    secrets:
      token: ${{ secrets.token }}
    with:  
      owner: ${{ vars.owner }}
      repository: ${{ vars.repository }}
```

**NOTE:** by using the `workflow_dispatch` event, this workflow can also be triggered manually.

Workflow [secrets](https://docs.github.com/en/actions/security-guides/using-secrets-in-github-actions) and [variables](https://docs.github.com/en/actions/learn-github-actions/variables):

- `secrets.token` - required: `true`, the [token](https://github.com/settings/tokens) must have permissions to [create a repository dispatch event](https://docs.github.com/en/rest/repos/repos#create-a-repository-dispatch-event)
- `vars.owner` - required: `false`, default: `github.repository_owner`
- `vars.repository` - required: `true`

## Requirements

- PHP >= `7.4` version.
- Composer ^`2.0` version.

## License

[MIT](license.txt)