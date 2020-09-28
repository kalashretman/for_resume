<?php

class PdfController extends Controller
{

    private $get_roles_for_block_action = array(19,21,40); //список ролей для доступа
    private $role = array(21); // список категорий для доступа

    public function beforeAction($action)
    {
        /*Подключение скрипта работы с ПДФ*/
        Yii::app()->clientScript->registerScriptFile('/PDFObject/pdfobject.js', CClientScript::POS_END);
        return true;
    }

    public function actionIndex()
    {

        $this->setPageTitle(Yii::t('workplace',  'programs_in_pdf')); // название рабочего места

        // проверка доступа по категориям
        if (Yii::app()->User->data->groups == $this->role){
            $this->checkInstructorCategoryByID(
                Yii::app()->User->data->instructorID
            );
        }

        if (Yii::app()->session['programpdf_pdf']) {
            Yii::app()->clientScript->registerScript(
                'programpdf',
                "var pdfName = ".json_encode(Yii::app()->session['programpdf_pdf']).";",
                CClientScript::POS_BEGIN
            );
        }

        //проприсовка структуры дерева названий Инструкций
        $list = $this->prepareTree(Irm::getAllIrm());
        $access = OtherMethods::getAccessFromRoleArr(
            $this->get_roles_for_block_action
        );

        $this->render('index', array(
            'access'  => $access,
            'pdfModel' => new AddImage('pdf'),
            'itemsArray' =>  $this->buildTree($list)
        ));
    }

    /**
     * получаем ПДФ-инструкцию по его id
     */
    public function actionGetPdfInfo()
    {
        if (Yii::app()->request->isAjaxRequest){
            $pdfID = Yii::app()->request->getPost('ID');
            if ($pdfID){
                echo CJSON::encode(
                    Irm::getPdfUrl($pdfID, true)
                );
            }
        }

        Yii::app()->end();
    }

    /**
     * NOTE: загрузка PDF на сервер
     */
    public function actionAddpdf()
    {
        // блокировка выполнения экшена для ролей get_roles_for_block_action
        OtherMethods::blockActionForRole($this->get_roles_for_block_action);

        $pdfID = Yii::app()->request->getPost('irmID');
        $dirForPdf = "programpdfs/";
        $model = new AddImage('pdf');
        $img_form = Yii::app()->request->getPost('AddImage');
        if(isset($img_form)){
            $model->attributes = $_POST['Item'];
            $model->image = CUploadedFile::getInstance($model,'image');

            $type = $model->image->getType();
            if ($type == 'application/pdf') {
                $nameFile = rand(0, 100000) . "." . substr($model->image->getType(), 12);
                $path = $dirForPdf . $nameFile;
                $model->image->saveAs($path);
                Irm::addPdfUrl($pdfID, $nameFile, $dirForPdf);
                Yii::app()->session['programpdf_pdf'] = $nameFile;
                OtherMethods::setMessageOperation('success', 'Файл PDF завантажено!');
            } else
                OtherMethods::setMessageOperation('error', 'Даний файл не є PDF. Завантажте файл в форматі PDF');
        }

        $this->redirect(Yii::app()->createUrl("/".$this->id));
    }

    /**
     * создаем массив для виджета
     * @param $arr данные по программам
     * @return array выходной массив
     */
    public function buildTree($arr)
    {
        foreach ($arr[0] as $key=>$value){
            $arrayA[] = array(
                'id' => $value['id'],
                'label'=>$value['irm'],
                'url'=>array('#'),
                'items'=>$this->itemsMenu($arr[$value['id']]),
            );
        }

        return $arrayA;
    }

    /**
     * разделяем массив по parents
     * @param $programs
     * @return array
     */
    public function prepareTree($programs)
    {
        $arr = array();
        foreach($programs as $program)
        {
            if (!$program['parent'])
                $program['parent'] = 0;
            if(empty($arr[$program['parent']]))
                $arr[$program['parent']] = array();
            $arr[$program['parent']][] = $program;
        }
        return $arr;
    }

    /**
     * создаем подпункты меню
     * @param $programArray
     * @return array
     */
    private function itemsMenu($programArray){
        $itemsArray = array();
        foreach ($programArray as $one) {
            $itemsArray[] = array(
                'label' => $one['irm'],
                'irmID' =>$one['id'],
                'pdf'=>$one['pdf'],
            );
        }
        return $itemsArray;
    }

    /**
     * Проверка категории инструктора. Разрешен доступ для КВ
     * @param $instructorID int id инструктора
     */
    public function checkInstructorCategoryByID($instructorID)
    {
        $instrInfo = TeachInstructors::getInstructorById($instructorID);
        if ($instrInfo['categoryID'] != 6)
            $this->redirect(Yii::app()->getBaseUrl(true));
    }
}