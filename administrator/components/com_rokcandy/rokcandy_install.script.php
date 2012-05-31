<?php
/**
 * Document Description
 *
 * Document Long Description
 *
 * PHP4/5
 *
 * Created on Jul 7, 2008
 *
 * @package package_name
 * @author Your Name <author@example.com>
 * @author Author Name
 * @license GNU/GPL http://www.gnu.org/licenses/gpl.html
 * @copyright 2009 Developer Name
 * @version SVN: $Id: file.script.php 17069 2010-05-15 03:58:31Z pasamio $
 */

class Com_RokCandyInstallerScript
{

    protected $parent;

    public function install($parent)
    {
        $this->parent = $parent;

        $basicid = $this->save_category(array(
             'extension' => 'com_rokcandy',
             'title' => 'Basic',
             'alias' => 'basic',
             'parent_id' => 1,
             'published' => 1,
             'id' => 0));

        $this->save_category(array(
              'extension' => 'com_rokcandy',
              'title' => 'Typography',
              'alias' => 'typography',
              'parent_id' => 1,
              'published' => 1,
              'id' => 0));

        $this->save_category(array(
              'extension' => 'com_rokcandy',
              'title' => 'Uncategorised',
              'alias' => 'uncategorised',
              'parent_id' => 1,
              'published' => 1,
              'id' => 0));

        JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_rokcandy/tables');
        $table = JTable::getInstance('category');
        $table->rebuild();
        $table = JTable::getInstance('menu');
        $table->rebuild();
        $table = JTable::getInstance('asset');
        $table->rebuild();

        $this->addMacro($basicid, '[code]{text}[/code]', '<code>{text}</code>', '7');
        $this->addMacro($basicid, '[i]{text}[/i]', '<em>{text}</em>', '6');
        $this->addMacro($basicid, '[b]{text}[/b]', '<strong>{text}</strong>', '5');
        $this->addMacro($basicid, '[h4]{text}[/h4]', '<h4>{text}</h4>', '4');
        $this->addMacro($basicid, '[h1]{text}[/h1]', '<h1>{text}</h1>', '1');
        $this->addMacro($basicid, '[h2]{text}[/h2]', '<h2>{text}</h2>', '2');
        $this->addMacro($basicid, '[h3]{text}[/h3]', '<h3>{text}</h3>', '3');
        return true;

    }

    protected function addMacro($catid, $macro, $html, $order)
    {
        $candytable = JTable::getInstance('candymacro', 'Table');
        $candytable->catid = $catid;
        $candytable->macro = $macro;
        $candytable->html = $html;
        $candytable->published = 1;
        $candytable->ordering = $order;
        $candytable->store();
    }

    protected function save_category($data)
    {
        // Initialise variables;
        $dispatcher = JDispatcher::getInstance();
        $table = JTable::getInstance('category');
        $pk = (!empty($data['id'])) ? $data['id'] : 0;
        $isNew = true;

        // Include the content plugins for the on save events.
        JPluginHelper::importPlugin('content');

        // Load the row if saving an existing category.
        if ($pk > 0)
        {
            $table->load($pk);
            $isNew = false;
        }

        // Set the new parent id if parent id not matched OR while New/Save as Copy .
        if ($table->parent_id != $data['parent_id'] || $data['id'] == 0)
        {
            $table->setLocation($data['parent_id'], 'last-child');
        }

        // Alter the title for save as copy
        if (!$isNew && $data['id'] == 0 && $table->parent_id == $data['parent_id'])
        {
            $m = null;
            $data['alias'] = '';
            if (preg_match('#\((\d+)\)$#', $table->title, $m))
            {
                $data['title'] = preg_replace('#\(\d+\)$#', '(' . ($m[1] + 1) . ')', $table->title);
            }
            else
            {
                $data['title'] .= ' (2)';
            }
        }

        // Bind the data.
        if (!$table->bind($data))
        {
            $this->parent->setError($table->getError());
            return false;
        }

        // Bind the rules.
        if (isset($data['rules']))
        {
            $rules = new JRules($data['rules']);
            $table->setRules($rules);
        }

        // Check the data.
        if (!$table->check())
        {
            $this->parent->setError($table->getError());
            return false;
        }

        // Trigger the onContentBeforeSave event.
        $result = $dispatcher->trigger('onContentBeforeSave', array('com_category.category', &$table, $isNew));
        if (in_array(false, $result, true))
        {
            $this->parent->setError($table->getError());
            return false;
        }

        // Store the data.
        if (!$table->store())
        {
            $this->parent->setError($table->getError());
            return false;
        }

        // Trigger the onContentAfterSave event.
        $dispatcher->trigger('onContentAfterSave', array('com_category.category', &$table, $isNew));

        // Rebuild the tree path.
        if (!$table->rebuildPath($table->id))
        {
            $this->parent->setError($table->getError());
            return false;
        }

        return $table->id;
    }


    public function uninstall($parent)
    {
        $dispatcher = JDispatcher::getInstance();
        $table = JTable::getInstance('category');
    }
}
