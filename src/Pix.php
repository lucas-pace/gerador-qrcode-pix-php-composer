<?php

namespace LucasPace\QRCode;

use chillerlan\QRCode\QRCode;

class Pix
{
    private $payload;

    public function __construct()
    {
        $this->payload[00] = "01"; // Payload Format Indicator, Obrigatório, valor fixo: 01
        $this->payload[26][00] = "BR.GOV.BCB.PIX"; // Indica arranjo específico; “00” (GUI) obrigatório e valor fixo: br.gov.bcb.pix
        $this->payload[52] = "0000"; // Merchant Category Code “0000” ou MCC ISO18245
        $this->payload[53] = "986"; // Moeda, “986” = BRL: real brasileiro - ISO4217
        $this->payload[54] = "0.00"; // Valor da transação, se comentado o cliente especifica o valor da transação no próprio app. Utilizar o . como separador decimal. Máximo: 13 caracteres.
        $this->payload[58] = "BR"; // “BR” – Código de país ISO3166-1 alpha 2
        $this->payload[62][05] = "***"; // Identificador de transação, quando gerado automaticamente usar ***. Limite 25 caracteres.
    }

    public function setPagamentoUnico($pagamentoUnico = true)
    {
        $this->payload[01] = $pagamentoUnico ? "12" : null;
    }

    public function montaPix(): string
    {
        $ret = "";

        foreach ($this->payload as $k => $v) {
            if (!is_array($v)) {
                $v = ($k == 54) ? number_format($v, 2, '.', '') : $this->removeCharEspeciais($v);
                $ret .= $this->c2($k) . $this->cpm($v) . $v;
            } else {
                $conteudo = $this->montaPixRecursive($v);
                $ret .= $this::c2($k) . $this->cpm($conteudo) . $conteudo;
            }
        }

        $ret .= "6304";
        $ret .= $this->crcChecksum($ret);

        return $ret;
    }

    private function montaPixRecursive($px)
    {
        $ret = "";

        foreach ($px as $k => $v) {
            if (!is_array($v)) {
                $v = ($k == 54) ? number_format($v, 2, '.', '') : $this->removeCharEspeciais($v);
                $ret .= $this->c2($k) . $this->cpm($v) . $v;
            } else {
                $conteudo = $this->montaPixRecursive($v);
                $ret .= $this->c2($k) . $this->cpm($conteudo) . $conteudo;
            }
        }

        return $ret;
    }

    private function removeCharEspeciais($txt)
    {
        return preg_replace('/[^a-zA-Z0-9@!#$%&\'*+\/=?^_`{|}~.\-\/]/', '', $txt);
    }

    private function corrigeCharEspeciais($texto)
    {
        $map = [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', 'æ' => 'a', 'ã' => 'a', 'å' => 'a', 'ā' => 'a',
            'ç' => 'c', 'ć' => 'c', 'č' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ē' => 'e', 'ė' => 'e', 'ę' => 'e',
            'î' => 'i', 'ï' => 'i', 'í' => 'i', 'ī' => 'i', 'į' => 'i', 'ì' => 'i',
            'ł' => 'l',
            'ñ' => 'n', 'ń' => 'n',
            'ô' => 'o', 'ö' => 'o', 'ò' => 'o', 'ó' => 'o', 'œ' => 'o', 'ø' => 'o', 'ō' => 'o', 'õ' => 'o',
            'ß' => 'ss',
            'ś' => 's', 'š' => 's',
            'û' => 'u', 'ü' => 'u', 'ù' => 'u', 'ú' => 'u', 'ū' => 'u',
            'ÿ' => 'y',
            'ž' => 'z', 'ź' => 'z', 'ż' => 'z',
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ä' => 'A', 'Æ' => 'A', 'Ã' => 'A', 'Å' => 'A', 'Ā' => 'A',
            'Ç' => 'C', 'Ć' => 'C', 'Č' => 'C',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ē' => 'E', 'Ė' => 'E', 'Ę' => 'E',
            'Î' => 'I', 'Ï' => 'I', 'Í' => 'I', 'Ī' => 'I', 'Į' => 'I', 'Ì' => 'I',
            'Ł' => 'L',
            'Ñ' => 'N', 'Ń' => 'N',
            'Ô' => 'O', 'Ö' => 'O', 'Ò' => 'O', 'Ó' => 'O', 'Œ' => 'O', 'Ø' => 'O', 'Ō' => 'O', 'Õ' => 'O',
            'Ś' => 'S', 'Š' => 'S',
            'Û' => 'U', 'Ü' => 'U', 'Ù' => 'U', 'Ú' => 'U', 'Ū' => 'U',
            'Ÿ' => 'Y',
            'Ž' => 'Z', 'Ź' => 'Z', 'Ż' => 'Z',
            'ᵃ' => 'a', 'ᵇ' => 'b', 'ᶜ' => 'c', 'ᵈ' => 'd', 'ᵉ' => 'e', 'ᶠ' => 'f', 'ᵍ' => 'g', 'ʰ' => 'h', '°' => 'o', 'ⁿ' => 'n',
            '¹' => '1', '²' => '2','³' => '3', '⁴' => '4', '⁵' => '5', '⁶' => '6', '⁷' => '7', '⁸' => '8', '⁹' => '9',
        ];

        return $this->removeCharEspeciais(strtr($texto, $map));
    }

    private function cpm($tx)
    {
        if (strlen($tx) > 99) {
            throw new \InvalidArgumentException("Tamanho máximo deve ser 99, inválido: $tx possui " . strlen($tx) . " caracteres.");
        }

        return $this->c2(strlen($tx));
    }

    private function c2($input)
    {
        return str_pad($input, 2, "0", STR_PAD_LEFT);
    }

    private function crcChecksum($str)
    {
        $crc = 0xFFFF;
        $strlen = strlen($str);

        for ($c = 0; $c < $strlen; $c++) {
            $crc ^= ord($str[$c]) << 8;

            for ($i = 0; $i < 8; $i++) {
                if ($crc & 0x8000) {
                    $crc = ($crc << 1) ^ 0x1021;
                } else {
                    $crc = $crc << 1;
                }
            }
        }

        $hex = $crc & 0xFFFF;
        $hex = dechex($hex);
        $hex = strtoupper(str_pad($hex, 4, '0', STR_PAD_LEFT));

        return $hex;
    }

    public function setChave($chave)
    {
        $this->payload[26][01] = $chave;
    }

    public function setNome($nome)
    {
        $this->payload[59] = $nome;
    }

    public function setCidade($cidade)
    {
        $this->payload[60] = $cidade;
    }

    public function setValor($valor)
    {
        $this->payload[54] = $valor;
    }

    public function setTransacaoId($transacaoId)
    {
        $this->payload[62][05] = $transacaoId;
    }

    public function render()
    {
        return (new QRCode)->render($this->montaPix());
    }

    public function setDescricao($descricao)
    {
        $this->payload[26][02] = $descricao;
    }
}
