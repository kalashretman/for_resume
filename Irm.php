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
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'irm';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('parent', 'numerical', 'integerOnly'=>true),
            array('irm, pdf', 'length', 'max'=>255),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, irm, pdf, parent', 'safe', 'on'=>'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'irm' => 'Irm',
            'pdf' => 'Pdf',
            'parent' => 'Parent',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('irm',$this->irm,true);
        $criteria->compare('pdf',$this->pdf,true);
        $criteria->compare('parent',$this->parent);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Irm the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

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
