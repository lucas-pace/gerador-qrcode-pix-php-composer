# Gerador de QRCode Pix

Gerador criado para facilitar a criação de QRCode do [PIX do Banco Central](https://www.bcb.gov.br/estabilidadefinanceira/pix) para pagamentos. Diferentemente de outras soluções já criadas para o mesmo propósito, este pacote abstrai as complexidades de montar a estrutura de um Pix e utiliza pacotes mais modernos.

[![PHP Version Support][php-badge]][php]
[![Packagist version][packagist-badge]][packagist]

[php-badge]: https://img.shields.io/packagist/php-v/lucas-pace/qrcode-pix?logo=php&color=8892BF
[php]: https://www.php.net/supported-versions.php
[packagist-badge]: https://img.shields.io/packagist/v/lucas-pace/qrcode-pix.svg?logo=packagist
[packagist]: https://packagist.org/packages/lucas-pace/qrcode-pix

### Requerimentos

-   PHP ^7.4
    -   [`ext-mbstring`](https://www.php.net/manual/book.mbstring.php)

### Instalação

```shell
composer require lucas-pace/qrcode-pix
```

### Utilização Básica

```php
    $pix = new Pix();
    $pix->setChave('exemplo@gmail.com');
    $pix->setNome('Lucas Pace');
    $pix->setCidade('Juiz de Fora');


    // Salvar o arquivo
    file_put_contents('qrcode.svg', file_get_contents($pix->render()));

    // Exibi-lo
    echo '<img src="' . $pix->render() . '" alt="QR Code" style="height: 200px"/>';

```
### Utilização completa
  - ```setValor(10.00)```
    - Define o valor da transação
    - Se nulo o cliente especificará o valor da transação no próprio app.
    - Deve utilizar ```.``` como separador decimal.
  - ```setChave('suachave')```
    - Define a chave pix do destinatário.
    - Podem ser utilizados:
      - Email
        - Tamanho máximo: 77 caracteres 
      - CPF e CNPJ
        - Somente números
      - Celular
        - Codificar no padrão internacional: "+5599888887777"
      - Chave Aleatória
  - ```setPagamentoUnico()```
    - Se utilizado, o QRCode só funcionará para a primeira transação feita.
  - ```setTransacaoId('123456') ```
    - Define um identificador para as transações. Por padrão *** ( sinalização para gerar automáticamente )
    - Limite 25 caracteres
  - ```setNome('Lucas Pace')```
    - Define o nome do destinatário.
  -  ```setCidade('Juiz de Fora')```
    - Define a cidade do destinatário


### Observações Importantes
    - Para bancos Itau, o identificador da transação deve ser obrigatóriamente ***. 


### Disclaimer 

O projeto foi inspirado no [gerador de QRCode Pix](https://github.com/renatomb/php_qrcode_pix). A motivação do projeto foi criar uma solução mais simples e moderna que utiliza o Composer para a instalação e gerenciamento de dependências, além de utilizar classe para abstrair a complexidade e tornar intuitiva montagem da estrutura do Pix.
