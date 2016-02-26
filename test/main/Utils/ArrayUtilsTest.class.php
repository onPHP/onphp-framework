<?php

final class ArrayUtilsTest extends TestCase
{
    /**
     * @dataProvider dateObjectsSortedLists
     **/
    public function testMergeDateSortedLists($list1, $list2, $method, $result, $limit)
    {
        $this->assertEquals(
            $result,
            ArrayUtils::mergeSortedLists(
                $list1,
                $list2,
                DateObjectComparator::me(),
                $method,
                $limit
            )
        );
    }

    public static function dateObjectsSortedLists()
    {
        $today = Date::makeToday();

        return
            [
                [
                    [
                        SortableObjectForTheTest::create()->setDate($today),
                        SortableObjectForTheTest::create()->setDate($today->spawn('-1 day')),
                        SortableObjectForTheTest::create()->setDate($today->spawn('-3 day'))
                    ],
                    [
                        SortableObjectForTheTest::create()->setDate($today->spawn('-1 day')),
                        SortableObjectForTheTest::create()->setDate($today->spawn('-2 day')),
                        SortableObjectForTheTest::create()->setDate($today->spawn('-2 day'))
                    ],
                    'getDate',
                    [
                        SortableObjectForTheTest::create()->setDate($today),
                        SortableObjectForTheTest::create()->setDate($today->spawn('-1 day')),
                        SortableObjectForTheTest::create()->setDate($today->spawn('-1 day')),
                        SortableObjectForTheTest::create()->setDate($today->spawn('-2 day')),
                        SortableObjectForTheTest::create()->setDate($today->spawn('-2 day')),
                        SortableObjectForTheTest::create()->setDate($today->spawn('-3 day'))
                    ],
                    null
                ],
                [
                    [
                        SortableObjectForTheTest::create()->setDate($today),
                        SortableObjectForTheTest::create()->setDate($today->spawn('-1 day')),
                        SortableObjectForTheTest::create()->setDate($today->spawn('-3 day'))
                    ],
                    [
                        SortableObjectForTheTest::create()->setDate($today->spawn('-2 day')),
                    ],
                    'getDate',
                    [
                        SortableObjectForTheTest::create()->setDate($today),
                        SortableObjectForTheTest::create()->setDate($today->spawn('-1 day')),
                        SortableObjectForTheTest::create()->setDate($today->spawn('-2 day')),
                        SortableObjectForTheTest::create()->setDate($today->spawn('-3 day'))
                    ],
                    null
                ],
                [
                    [
                        SortableObjectForTheTest::create()->setDate($today->spawn('-1 day')),
                        SortableObjectForTheTest::create()->setDate($today->spawn('-3 day'))
                    ],
                    [
                        SortableObjectForTheTest::create()->setDate($today),
                        SortableObjectForTheTest::create()->setDate($today->spawn('-4 day')),
                        SortableObjectForTheTest::create()->setDate($today->spawn('-6 day')),
                    ],
                    'getDate',
                    [
                        SortableObjectForTheTest::create()->setDate($today),
                        SortableObjectForTheTest::create()->setDate($today->spawn('-1 day')),
                        SortableObjectForTheTest::create()->setDate($today->spawn('-3 day')),
                        SortableObjectForTheTest::create()->setDate($today->spawn('-4 day')),
                        SortableObjectForTheTest::create()->setDate($today->spawn('-6 day'))
                    ],
                    null
                ],
                [
                    [
                        SortableObjectForTheTest::create()->setDate($today->spawn('-1 day')),
                        SortableObjectForTheTest::create()->setDate($today->spawn('-3 day'))
                    ],
                    [
                        SortableObjectForTheTest::create()->setDate($today),
                        SortableObjectForTheTest::create()->setDate($today->spawn('-4 day')),
                        SortableObjectForTheTest::create()->setDate($today->spawn('-6 day')),
                    ],
                    'getDate',
                    [
                        SortableObjectForTheTest::create()->setDate($today),
                        SortableObjectForTheTest::create()->setDate($today->spawn('-1 day')),
                    ],
                    2
                ],
                [
                    [
                        SortableObjectForTheTest::create()->setDate($today->spawn('-1 day')),
                    ],
                    [
                        SortableObjectForTheTest::create()->setDate($today),
                        SortableObjectForTheTest::create()->setDate($today->spawn('-4 day')),
                        SortableObjectForTheTest::create()->setDate($today->spawn('-6 day')),
                    ],
                    'getDate',
                    [
                        SortableObjectForTheTest::create()->setDate($today),
                        SortableObjectForTheTest::create()->setDate($today->spawn('-1 day')),
                        SortableObjectForTheTest::create()->setDate($today->spawn('-4 day'))
                    ],
                    3
                ]
            ];
    }

    /**
     * @dataProvider textDataSortedLists
     **/
    public function testMergeTextDataSortedLists($list1, $list2, $method, $result, $limit)
    {
        $this->assertEquals(
            $result,
            ArrayUtils::mergeSortedLists(
                $list1,
                $list2,
                StandardComparator::me(),
                $method,
                $limit
            )
        );
    }

    public function testConvertObjectList()
    {
        $list =
            [
                TestCity::create()->setId(42)->setName('Beldyazki'),
                TestCity::create()->setId(666)->setName('Moscow')
            ];

        $this->assertEquals([42, 666], array_keys(ArrayUtils::convertObjectList($list)));
        $this->assertEquals(['Beldyazki', 'Moscow'], array_keys(ArrayUtils::convertObjectList($list, 'getName')));

    }

    public static function textDataSortedLists()
    {
        return
            [
                [
                    [
                        SortableTextDataObjectForTheTest::create()->setData('SIBN'),
                        SortableTextDataObjectForTheTest::create()->setData('SBER03'),
                        SortableTextDataObjectForTheTest::create()->setData('HYDR')
                    ],
                    [
                        SortableTextDataObjectForTheTest::create()->setData('MTSI'),
                        SortableTextDataObjectForTheTest::create()->setData('GAZP')
                    ],
                    'getData',
                    [
                        SortableTextDataObjectForTheTest::create()->setData('SIBN'),
                        SortableTextDataObjectForTheTest::create()->setData('SBER03'),
                        SortableTextDataObjectForTheTest::create()->setData('MTSI'),
                        SortableTextDataObjectForTheTest::create()->setData('HYDR'),
                        SortableTextDataObjectForTheTest::create()->setData('GAZP')
                    ],
                    null
                ]
            ];
    }
}

// for the test
final class SortableObjectForTheTest
{
    private $date = null;


    public function setDate(Date $date)
    {
        $this->date = $date;

        return $this;
    }

    public function getDate()
    {
        return $this->date;
    }
}

final class SortableTextDataObjectForTheTest
{
    private $data = null;


    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function getData()
    {
        return $this->data;
    }
}

?>