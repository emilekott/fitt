<?php
 /**
 * @version   $Id$
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class Doctrine_Template_Listener_Orderable extends Doctrine_Record_Listener
{
    /**
     * Array of orderable options
     *
     * @var string
     */
    protected $_options = array();

    /**
     * __construct
     *
     * @param array|string $options
     * @return Doctrine_Template_Listener_Orderable
     */
    public function __construct(array $options)
    {
        $this->_options = $options;
    }

    /**
     * Set the Orderable columns when a record is inserted
     *
     * @param Doctrine_Event $event
     * @return void
     */
    public function preInsert(Doctrine_Event $event)
    {
        $record = $event->getInvoker();
        $name = $record->getTable()->getFieldName($this->_options['name']);
        $modified = $record->getModified();

        $conn = $record->getTable()->getConnection();
        try
        {
            $conn->beginInternalTransaction();
            // Figure out what the
            if (!isset($modified[$name])) {
                if ($this->_options['initialPosition'] == 'last') {
                    $highest = $this->getHighestPosition($record);
                    if (is_numeric($highest)) {
                        $initialPosition = $highest + 1;
                    }
                    else
                    {
                        $initialPosition = 0;
                    }
                    $record->$name = $initialPosition;
                }
                else
                {
                    // shift higher records
                    $record->$name = 0;
                    $this->shiftOrder($record);

                }
            }
            else
            {
                $this->shiftOrder($record);
            }
            $conn->commit();
        } catch (Exception $e)
        {
            $conn->rollback();
            throw $e;
        }
    }

    public function postInsert(Doctrine_Event $event)
    {
        $record = $event->getInvoker();
        $conn = $record->getTable()->getConnection();
        try
        {
            $conn->beginInternalTransaction();
            $this->reorder($record);
            $conn->commit();
        } catch (Exception $e)
        {
            $conn->rollback();
            throw $e;
        }
    }

    /**
     * @param Doctrine_Event $event
     * @return void
     */
    public function preUpdate(Doctrine_Event $event)
    {
        $record = $event->getInvoker();
        $name = $record->getTable()->getFieldName($this->_options['name']);
        $modified = $record->getModified();
        $original = $record->getModified(true);

        if (array_key_exists($name, $modified)) {
            $table = $this->getTableForRecord($record);
            $name = $table->getFieldName($this->_options['name']);
            $conn = $record->getTable()->getConnection();
            try
            {
                $conn->beginInternalTransaction();
                $this->shiftOrder($record);

                $conn->commit();
            }
            catch (Exception $e)
            {
                $conn->rollback();
                throw $e;
            }

        }
    }

    /**
     * @param Doctrine_Event $event
     * @return void
     */
    public function postUpdate(Doctrine_Event $event)
    {
        $record = $event->getInvoker();
        $conn = $record->getTable()->getConnection();
        try
        {
            $conn->beginInternalTransaction();
            $this->reorder($record);
            $conn->commit();
        } catch (Exception $e)
        {
            $conn->rollback();
            throw $e;
        }
    }


    /**
     * @param Doctrine_Record $record
     */
    protected function reorder(Doctrine_Record $record)
    {
        $table = $this->getTableForRecord($record);
        $name = $table->getFieldName($this->_options['name']);


        $key_columns = $table->getIdentifier();

        if (!is_array($key_columns)) {
            $key_columns = array($key_columns);
        }
        $ret_columns = array_merge($key_columns, array($name));

        /** @var Doctrine_Query $query  */
        $query = $table->createQuery()
                ->select(implode(',', $ret_columns))
                ->where($name . '>= 0')
                ->orderBy($name);

        foreach ($this->_options['orderableBy'] as $orderBy) {
            if (is_null($record->$orderBy)) {
                $query->andWhere($orderBy . ' IS NULL');
            } else {
                $value = $record->$orderBy;
                if ($value instanceof Doctrine_Record) {
                    $value = current((array)$value->identifier());
                }
                $query->andWhere($orderBy . ' = ?', $value);
            }
        }

        $reorderables = $query->execute(array(), Doctrine_Core::HYDRATE_SCALAR);
        $query->free();

        $component_name = $table->getComponentName();

        $retmap = array();
        foreach ($key_columns as $key_column)
            $retmap[$component_name . '_' . $key_column] = $key_column;


        $ordering = 0;
        foreach ($reorderables as $reorder)
        {
            /** @var Doctrine_Query $query  */
            $query = $table->createQuery()
                    ->update()
                    ->set($name, $ordering)
                    ->where($name . ' >= 0');
            foreach ($retmap as $retcol => $id_field)
            {
                $query->andWhere($id_field . ' = ' . $reorder[$retcol]);
            }
            $effected = $query->execute();
            $ordering++;
        }
    }

    /**
     * @param Doctrine_Record $record
     * @return integer
     */
    protected function getHighestPosition(Doctrine_Record $record)
    {

        $table = $this->getTableForRecord($record);

        $name = $table->getFieldName($this->_options['name']);

        $query = $table->createQuery('r')
                ->select('MAX(r.' . $name . ')');

        foreach ($this->_options['orderableBy'] as $orderBy) {
            if (is_null($record->$orderBy)) {
                $query->andWhere('r.' . $orderBy . ' IS NULL');
            } else {
                $value = $record->$orderBy;
                if ($value instanceof Doctrine_Record) {
                    $value = current((array)$value->identifier());
                }
                $query->andWhere('r.' . $orderBy . ' = ?', $value);
            }
        }

        $max_order = $query->fetchOne(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR);
        $query->free();
        return $max_order;
    }

    /**
     * @param Doctrine_Record $record
     */
    protected function shiftOrder(Doctrine_Record $record)
    {
        $table = $this->getTableForRecord($record);

        $name = $table->getFieldName($this->_options['name']);

        $query = $table->createQuery()
                ->update()
                ->set($name, '1+' . $name)
                ->where($name . ' >= ' . $record->$name);

        foreach ($this->_options['orderableBy'] as $orderBy) {
            if (is_null($record->$orderBy)) {
                $query->andWhere($orderBy . ' IS NULL');
            } else {
                $value = $record->$orderBy;
                if ($value instanceof Doctrine_Record) {
                    $value = current((array)$value->identifier());
                }
                $query->andWhere($orderBy . ' = ?', $value);
            }
        }

        $results = $query->execute();
    }


    /**
     * @param Doctrine_Record $record
     * @return Doctrine_Table
     */
    protected function getTableForRecord(Doctrine_Record $record)
    {
        /* fix for use with Column Aggregation Inheritance */
        if ($record->getTable()->getOption('inheritanceMap')) {
            $parentTable = $record->getTable()->getOption('parents');
            $i = 0;
            // Be sure that you do not instanciate an abstract class;
            $reflectionClass = new ReflectionClass($parentTable[$i]);
            while ($reflectionClass->isAbstract())
            {
                $i++;
                $reflectionClass = new ReflectionClass($parentTable[$i]);
            }
            $table = Doctrine_Core::getTable($parentTable[$i]);
        } else
        {
            $table = $record->getTable();
        }

        return $table;
    }
}
