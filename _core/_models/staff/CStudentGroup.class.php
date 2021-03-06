<?php
/**
 * Created by JetBrains PhpStorm.
 * User: TERRAN
 * Date: 10.06.12
 * Time: 20:50
 * To change this template use File | Settings | File Templates.
 */
class CStudentGroup extends CActiveModel {
    protected $_table = TABLE_STUDENT_GROUPS;
    private $_students = null;
    private $_year = null;
    private $_speciality = null;
    protected $_monitor = null;
    protected $_curator = null;
    protected $_corriculum = null;
    public function relations() {
        return array(
            "monitor" => array(
                "relationPower" => RELATION_HAS_ONE,
                "storageProperty" => "_monitor",
                "storageField" => "head_student_id",
                "managerClass" => "CStaffManager",
                "managerGetObject" => "getStudent"
            ),
            "curator" => array(
                "relationPower" => RELATION_HAS_ONE,
                "storageProperty" => "_curator",
                "storageField" => "curator_id",
                "managerClass" => "CStaffManager",
                "managerGetObject" => "getPerson"
            ),
            "corriculum" => array(
                "relationPower" => RELATION_HAS_ONE,
                "storageProperty" => "_corriculum",
                "storageField" => "corriculum_id",
                "managerClass" => "CCorriculumsManager",
                "managerGetObject" => "getCorriculum"
            )
        );
    }
    public function attributeLabels() {
        return array(
            "name" => "Название",
            "students_cnt" => "Число студентов",
            "speciality_id" => "Специальность",
            "head_student_id" => "Староста",
            "year_id" => "Учебный год",
            "curator_id" => "Куратор",
            "comment" => "Комментарий",
            "corriculum_id" => "Учебный план"
        );
    }
    /**
     * Имя студента
     *
     * @return mixed
     */
    public function getName() {
        return $this->getRecord()->getItemValue("name");
    }
    /**
     * Учебный год
     *
     * @return CTerm
     */
    public function getYear() {
        if (is_null($this->_year)) {
            $this->_year = CTaxonomyManager::getYear($this->getRecord()->getItemValue("year_id"));
        }
        return $this->_year;
    }
    /**
     * @return array
     */
    public function toArrayForJSON() {
        $r['id'] = $this->getId();
        $r['value'] = $this->getName()." (".$this->getYear()->getValue().")";

        return $r;
    }
    /**
     * Все студенты группы.
     * Отсортированы по ФИО
     *
     * @return CArrayList
     */
    public function getStudents() {
        if (is_null($this->_students)) {
            $this->_students = new CArrayList();
            $tList = new CArrayList();
            foreach (CActiveRecordProvider::getWithCondition(TABLE_STUDENTS, "group_id=".$this->getId(), "fio asc")->getItems() as $item) {
                $student = new CStudent($item);
                $tList->add($student->getName(), $student->getId());
                CStaffManager::getCacheStudents()->add($student->getId(), $student);
            }
            foreach ($tList->getSortedByKey(true)->getItems() as $id) {
                $student = CStaffManager::getStudent($id);
                $this->_students->add($student->getId(), $student);
            }
        }
        return $this->_students;
    }
    /**
     * Специальность
     *
     * @return CTerm
     */
    public function getSpeciality() {
        if (is_null($this->_speciality)) {
            $this->_speciality = CTaxonomyManager::getSpeciality($this->getRecord()->getItemValue("speciality_id"));
        }
        return $this->_speciality;
    }
}
