<?php

namespace Psc\CMS\Item;

class Exporter extends \Psc\SimpleObject {
  
  public function convertToId(TabOpenable $item) {
    return str_replace(array('/', '@',    '.', '&', '?', '='),
                       array('-', '-at-', '-', '-', '-', '-'),
                       trim($item->getTabRequestMeta()->getURL(),'/')
                      );
  }
  
  public function TabOpenable(TabOpenable $item) {
    return array('tab'=>(object) array(
      'id'=>$this->convertToId($item),
      'label'=>$item->getTabLabel(),
      'url'=>$item->getTabRequestMeta()->getURL()
    ));
  }
  
  public function TabLinkable(TabLinkable $item) {
    return array_merge(
      $this->TabOpenable($item),
      array('link'=>(object) array(
        $item->getLinkLabel()
      ))
    );
  }
  
  public function Buttonable(Buttonable $item) {
    return array('button'=>(object) array(
      'label'=>$item->getButtonLabel(),
      'fullLabel'=>$item->getFullButtonLabel(),
      'mode'=>$item->getButtonMode(),
      'leftIcon'=>$item->getButtonLeftIcon(),
      'rightIcon'=>$item->getButtonRightIcon()
    ));
  }
  
  public function TabButtonable(TabButtonable $item) {
    return $this->merge($item, array('Buttonable','TabOpenable'));
  }

  public function DeleteButtonable(DeleteButtonable $item) {
    return $this->merge($item, array('Buttonable','Deleteable'));
  }

  public function Deleteable(DeleteButtonable $item) {
    return array_merge(
      $this->Identifyable($item),
      array('delete'=>(object) array(
        'url'=>$item->getDeleteRequestMeta()->getUrl(),
        'method'=>$item->getDeleteRequestMeta()->getMethod(),
      ))
    );
  }
  
  public function Identifyable(Identifyable $item) {
    return array(
      'identifier'=>$item->getIdentifier(),
      'entityName'=>$item->getEntityName()
    );
  }
  
  public function AutoCompletable(AutoCompletable $item) {
    return array(
      // performance für js - autocomplete von ui liest das hier am schnellsten aus
      // alternativ könnten wir in js iterieren (was aber käse ist)
      'label'=>$item->getAutoCompleteLabel(),
      'value'=>$item->getIdentifier(),
      
      'ac'=>(object) array(
        'label'=>$item->getAutoCompleteLabel()
      )
    );
  }
  
  /**
   *
   * wird z.b. von DropBox2 direkt benutzt
   */
  public function ComboDropBoxable(ComboDropBoxable $item) {
    return $this->merge($item, array('SelectComboBoxable','DropBoxButtonable'));
  }

  
  /**
   *
   * wird z.b. von DropBox2 direkt benutzt
   */
  public function SelectComboBoxable(SelectComboBoxable $item) {
    return $this->AutoCompletable($item);
  }

  public function DropBoxButtonable(DropBoxButtonable $item) {
    return $this->merge($item, array('Buttonable','TabOpenable','Identifyable'));
  }

  public function RightContentLinkable(RightContentLinkable $item) {
    return $this->merge($item, array('TabLinkable', 'Identifyable'));
  }
  
  /**
   * wird z.b. vom AbstractEntityController für Autocomplete direkt benutzt
   *
   * @TODO wir müssen alle Interfaces finden die von den angegebenen Interfaces abgeleitet werden um alles zu exporiteren
   * z. b. autocompletable exportiert nur die eigenen Properties. Dies muss implizit identifyable hinzufügen
   */
  public function merge($item, array $interfaces) {
    $export = array();
    foreach ($interfaces as $interface) {
      $export = array_merge($export, $this->$interface($item));
    }
    return $export;
  }
}
?>