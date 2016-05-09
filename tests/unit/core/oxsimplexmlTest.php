<?php
/**
 * This file is part of OXID eShop Community Edition.
 *
 * OXID eShop Community Edition is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eShop Community Edition is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eShop Community Edition.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2015
 * @version   OXID eShop CE
 */

require_once realpath( "." ).'/unit/OxidTestCase.php';

/**
 * Testing oxXml class.
 */
class Unit_Core_oxSimpleXmlTest extends OxidTestCase
{
    public function testObjectToXml()
    {
        $oXml = new oxSimpleXml();

        $oTestObject = new oxStdClass();
        $oTestObject->title = "TestTitle";
        $oTestObject->keys = new oxStdClass();
        $oTestObject->keys->key = array("testKey1", "testKey2");

        $sTestResult = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        $sTestResult .= "<testXml>";
        $sTestResult .= "<title>TestTitle</title>";
        $sTestResult .= "<keys><key>testKey1</key><key>testKey2</key></keys>";
        $sTestResult .= "</testXml>\n";

        $this->assertEquals($sTestResult, $oXml->objectToXml($oTestObject, "testXml"));
    }

    public function testObjectToXmlWithObjectsInArray()
    {
        $oXml = new oxSimpleXml();

        $oModule1 = new stdClass();
        $oModule1->id = "id1";
        $oModule1->active = true;

        $oModule2 = new stdClass();
        $oModule2->id = "id2";
        $oModule2->active = false;

        $oTestObject = new oxStdClass();
        $oTestObject->title = "TestTitle";
        $oTestObject->modules = new oxStdClass();
        $oTestObject->modules->module = array($oModule1, $oModule2);

        $oExpectedXml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\"?><testXml/>");
        $oExpectedXml->addChild("title", "TestTitle");
        $modules = $oExpectedXml->addChild("modules");

        $module = $modules->addChild("module");
        $module->addChild('id', 'id1');
        $module->addChild('active', '1');

        $module = $modules->addChild("module");
        $module->addChild('id', 'id2');
        $module->addChild('active', '');

        $this->assertEquals($oExpectedXml->asXML(), $oXml->objectToXml($oTestObject, "testXml"));
    }

    public function testXmlToObject()
    {
        $oXml = new oxSimpleXml();

        $sTestXml = '<?xml version="1.0"?>';
        $sTestXml .= '<testXml>';
        $sTestXml .= '<title>TestTitle</title>';
        $sTestXml .= '<keys><key>testKey1</key><key>testKey2</key></keys>';
        $sTestXml .= '</testXml>';

        $oRes = $oXml->xmlToObject($sTestXml);

        $this->assertEquals((string) $oRes->title, "TestTitle");
        $this->assertEquals((string) $oRes->keys->key[0], "testKey1");
        $this->assertEquals((string) $oRes->keys->key[1], "testKey2");
    }

    public function testObjectToXmlWithElementsWithAttributesKey()
    {
        $oXml = new oxSimpleXml();

        $oTestObject = new stdClass();
        $oTestObject->attributes = new stdClass();
        $oTestObject->attributes->attribute = array('attrValue1', 'attrValue2');

        $sTestResult = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        $sTestResult .= '<testXml>';
        $sTestResult .= '<attributes>';
        $sTestResult .= '<attribute>attrValue1</attribute>';
        $sTestResult .= '<attribute>attrValue2</attribute>';
        $sTestResult .= '</attributes>';
        $sTestResult .= '</testXml>' . "\n";

        $this->assertEquals($sTestResult, $oXml->objectToXml($oTestObject, "testXml"));
    }
}