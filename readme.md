# Hyperf Extractor

Este código apresenta uma extensão do [desafio técnico escrito em Go](https://github.com/vinaocruz/go-extractor), para importar milhões de registros no banco de dados a partir de arquivos txt. 

A ideia de reescrever utilizando php + hyperf é avaliar o comportamento de ambos cenários.

## Versões utilizadas

Para este projeto, foram utilizadas as seguintes versões:

* Docker 26.1
* Docker-compose 1.29
* PHP 8.3
* Hyperf 3

## Setup

Execute o docker-compose para subir o banco de dados postgres utilizado neste projeto. Ao criar o container, o sql da pasta initdb/ será executado no setup

```
docker-compose up -d
```


## Importação de dados

A base de dados será populada com os arquivos estáticos localizado na pasta `storage/example/` contendo +63M de registros. Para executar a importação do histórico de negociações da B3 dos últimos 7 dias, utilize:

```
docker exec -it hyperf-extractor php bin/hyperf.php app:dataImport
```

### Estratégia de otimização

TODO

### Estratégia de modelagem do banco

Para este código foi mantido apenas uma tabela, considerando que o código do ticket é uma chave que represente a empresa que opera na bolsa.

Foram criados dois índices para otimizar busca em `ticketCode` e `transationAt`

