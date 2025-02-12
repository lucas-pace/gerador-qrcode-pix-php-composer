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
        return preg_replace('/[^a-zA-Z0-9@!#$%&\'*+\/=?^_`{|}~.-\/]/', '', $this->removeAcentos($txt));
    }

    private function removeAcentos($texto)
    {
        $search = explode(",", "à,á,â,ä,æ,ã,å,ā,ç,ć,č,è,é,ê,ë,ē,ė,ę,î,ï,í,ī,į,ì,ł,ñ,ń,ô,ö,ò,ó,œ,ø,ō,õ,ß,ś,š,û,ü,ù,ú,ū,ÿ,ž,ź,ż,À,Á,Â,Ä,Æ,Ã,Å,Ā,Ç,Ć,Č,È,É,Ê,Ë,Ē,Ė,Ę,Î,Ï,Í,Ī,Į,Ì,Ł,Ñ,Ń,Ô,Ö,Ò,Ó,Œ,Ø,Ō,Õ,Ś,Š,Û,Ü,Ù,Ú,Ū,Ÿ,Ž,Ź,Ż");
        $replace = explode(",", "a,a,a,a,a,a,a,a,c,c,c,e,e,e,e,e,e,e,i,i,i,i,i,i,l,n,n,o,o,o,o,o,o,o,o,s,s,s,u,u,u,u,u,y,z,z,z,A,A,A,A,A,A,A,A,C,C,C,E,E,E,E,E,E,E,I,I,I,I,I,I,L,N,N,O,O,O,O,O,O,O,O,S,S,U,U,U,U,U,Y,Z,Z,Z");
        return $this->removeEmoji(str_replace($search, $replace, $texto));
    }

    private function removeEmoji($string)
    {
        return preg_replace(
            '%(?:
               \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
             | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
             | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
            )%xs',
            '  ',
            $string
        );
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
