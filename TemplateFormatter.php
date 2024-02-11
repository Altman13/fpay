<?php

namespace FpDbTest;

class TemplateFormatter
{
    public function convertPlaceholders($query, $params)
    {
        $typeOfPlaceHolder = [];

        preg_match_all('/\?#|\?d|\?f|\?a|\?/', $query, $matches);
        $modifiedQuery = $query;

        foreach ($matches[0] as $placeholder) {
            $param = array_shift($params);
            $modifiedQuery = $this->replacePlaceholder($modifiedQuery, $placeholder, $param, $typeOfPlaceHolder);
        }

        $modifiedQuery = str_replace(['{ AND block = __SKIP__}', '__SKIP__', '{', '}'], '', $modifiedQuery);
        return $modifiedQuery;
    }

    private function replacePlaceholder($query, $placeholder, $param, &$typeOfPlaceHolder)
    {
        switch ($placeholder) {
            case '?#':
                return $this->replaceIdentifierPlaceholder($query, $param, $typeOfPlaceHolder);
            case '?d':
                return $this->replaceIntegerPlaceholder($query, $param, $typeOfPlaceHolder);
            case '?f':
                return $this->replaceFloatPlaceholder($query, $param, $typeOfPlaceHolder);
            case '?a':
                return $this->replaceArrayPlaceholder($query, $param, $typeOfPlaceHolder);
            case '?':
                return $this->replaceStringPlaceholder($query, $param, $typeOfPlaceHolder);
            default:
                throw new \Exception("Неизвестный тип плейсхолдера: $placeholder");
        }
    }

    private function replaceIdentifierPlaceholder($query, $param, &$typeOfPlaceHolder)
    {
        $typeOfPlaceHolder[] = ' ' . 'идентификатор';
        $replacement = is_array($param) ? '`' . implode('`, `', $param) . '`' : "`$param`";
        return str_replace('?#', $replacement, $query);
    }

    private function replaceIntegerPlaceholder($query, $param, &$typeOfPlaceHolder)
    {
        $typeOfPlaceHolder[] = ' ' . 'целое число';
        $pos = strpos($query, '?d');
        if ($pos !== false) {
            if ($param instanceof SpecialValue) {
                $modifiedParam = $param->getValue();
            } else if (is_bool($param)) {
                $modifiedParam = $param ? 1 : 0;
            } else {
                $modifiedParam = ($param === null) ? 'NULL' : (int)$param;
            }
            return substr_replace($query, $modifiedParam, $pos, 2);
        }

        return $query;
    }
    private function replaceFloatPlaceholder($query, $param, &$typeOfPlaceHolder)
    {
        $typeOfPlaceHolder[] = ' ' . 'число с плавающей точкой';
        return str_replace('?f', (float)$param, $query);
    }

    private function replaceArrayPlaceholder($query, $param, &$typeOfPlaceHolder)
    {
        $typeOfPlaceHolder[] = ' ' . 'массив';

        if (!is_array($param)) {
            throw new \Exception("Параметр для спецификатора ?a должен быть массивом.");
        }

        $formattedParams = [];
        foreach ($param as $key => $value) {
            if (is_int($key)) {
                $formattedParams[] = ($value === null) ? 'NULL' : $value;
            } else {
                $formattedKey = "`" . $key . "`";
                $formattedValue = ($value === null) ? 'NULL' : "'" . ($value) . "'";
                $formattedParams[] = "$formattedKey = $formattedValue";
            }
        }

        return preg_replace('/\?a/', implode(', ', $formattedParams), $query, 1);
    }

    private function replaceStringPlaceholder($query, $param, &$typeOfPlaceHolder)
    {
        $typeOfPlaceHolder[] = ' ' . 'строка';
        $typeOfPlaceHolder[] = ' ' . $param;
        $paramWithSlashes = "'" . addslashes($param) . "'";
        return str_replace('?', $paramWithSlashes, $query);
    }
}
