<?php

/**
 * 递归父子数组结构
 * @param array $items 待转换的数组
 * @param string $id_name 主键id名
 * @param string $pid_name 父id名
 * @param string $children_name 子集数组名
 * @return array
 */
if (!function_exists('recursion_child')) {
    function recursion_child($items, $id_name = 'id', $pid_name = 'parent_id', $children_name = 'children')
    {
        $items_new = array();
        foreach ($items as $item) {
            $items_new[$item[$id_name]] = $item;
        }
        $items = $items_new;
        unset($items_new);
        $tree = array();
        foreach ($items as $item) {
            if (isset($items[$item[$pid_name]])) {
                $items[$item[$pid_name]][$children_name][] = &$items[$item[$id_name]];
            } else {
                $tree[] = &$items[$item[$id_name]];
            }
        }
        return $tree;
    }
}
