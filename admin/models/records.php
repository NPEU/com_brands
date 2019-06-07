<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_brands
 *
 * @copyright   Copyright (C) NPEU 2019.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

/**
 * Brands Records List Model
 */
class BrandsModelRecords extends JModelList
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @see     JController
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                'id',
                'state'
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to build an SQL query to load the list data.
     *
     * @return      string  An SQL query
     */
    protected function getListQuery()
    {
        // Initialize variables.
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Create the base select statement.
        $query->select('b.*')
              ->from($db->quoteName('#__brands') . ' AS b');

        // Join the categories table again for the project group:
        $query->select('c.title AS category')
            ->join('LEFT', '#__categories AS c ON c.id = b.catid');

        // Join over the users for the checked out user.
        $query->select('u.name AS editor')
            ->join('LEFT', '#__users AS u ON u.id=b.checked_out');


        // Filter by a single or group of categories.
        $categoryId = $this->getState('filter.category_id');

        if (is_numeric($categoryId))
        {
            $query->where($db->quoteName('b.catid') . ' = ' . (int) $categoryId);
        }
        elseif (is_array($categoryId))
        {
            $query->where($db->quoteName('b.catid') . ' IN (' . implode(',', ArrayHelper::toInteger($categoryId)) . ')');
        }

        // Filter: like / search
        $search = $this->getState('filter.search');

        if (!empty($search))
        {
            $like = $db->quote('%' . $search . '%');
            $query->where('b.name LIKE ' . $like);
            $query->where('b.alias LIKE ' . $like);
        }

        // Filter by state
        $state = $this->getState('filter.published');

        if (is_numeric($state))
        {
            $query->where('b.state = ' . (int) $state);
        }
        elseif ($state === '')
        {
            $query->where('(b.state IN (0, 1))');
        }

        // Add the list ordering clause.
        $orderCol   = $this->state->get('list.ordering', 'b.name');
        $orderDirn  = $this->state->get('list.direction', 'asc');

        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
