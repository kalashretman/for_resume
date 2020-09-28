<?php

/**
 * This is the model class for table "irm".
 *
 * The followings are the available columns in table 'irm':
 * @property integer $id
 * @property string $irm
 * @property string $pdf
 * @property integer $parent
 */
class Irm extends CActiveRecord
{
    /**
     * Добавление Ирм в базу. Если ирм уже существует, она будет удалена
     * @param $irmId номер Ирм
     * @param $nameFile название файла
     * @param $pdfPath путь к файлу
     */
    public static function addPdfUrl($irmId, $nameFile, $pdfPath)
    {
        //удаляем предыдущий PDF-файл
        if ($oldFile = self::getPdfUrl($irmId))
            @unlink($pdfPath.$oldFile);

        //записываем новое значение url PDF-файла
        self::setPdfUrl($irmId, $nameFile);
    }

    /**
     * получение ИРМ по ее id
     * в зависимости от значения флага $all_flag получаем все данные ИРМ или только значнеи поля pdf
     * @param $irmId
     * @param bool $all
     * @return mixed
     */
    public static function getPdfUrl($irmId, $all_flag=false)
    {
        $select = $all_flag?'*':'pdf';
        $sql = "SELECT $select FROM irm WHERE id=:id";
        $query = Yii::app()->db->createCommand($sql);
        $query->bindParam(':id', $irmId);
        $result = $query->queryRow();

        return $all_flag?$result:$result['pdf'];
    }

    /**
     * обновляем ИРМ по ее id
     * @param $id номер ИРМ
     * @param $pdfUrl новый путь к файлу ИРМ
     */
    public static function setPdfUrl($id, $pdfUrl)
    {
        $sql='UPDATE irm SET pdf=:pdfUrl WHERE id=:id';
        $query = Yii::app()->db->createCommand($sql);
        $query->execute(array(
            ':pdfUrl' => $pdfUrl,
            ':id'     => $id
        ));
    }

    /**
     * получаем все ИРМ
     * @return mixed
     */
    public static function getAllIrm()
    {
        $sql = "SELECT * FROM irm";
        $query = Yii::app()->db->createCommand($sql);

        return $query->queryAll();
    }
}
