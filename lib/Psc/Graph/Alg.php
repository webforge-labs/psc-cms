<?php

namespace Psc\Graph;

class Alg {

  /**
   * Breitensuche
   *
   * Algorithmus aus C. Cormen / E. Leiserson / R. Rivest / C. Stein: Algorithmen - Eine Einführung
   * @param Graph $g der Graph sollte nur aus GraphBFSVertice bestehen (das ist selber zu überprüfen aus Performancegründen)
   * @param GraphBFSVertice $s der Startknoten
   */
  public static function BFS(Graph $g, BFSVertice $s) {
    if (!$g->has($s)) throw new Exception('Startknoten '.$s.' ist nicht im Graphen g vorhanden');
  
    foreach($g->V() as $vertice) {
      $vertice->color = 'weiss';
      $vertice->d = PHP_INT_MAX;
      $vertice->parent = NULL;
    }

    $s->color = 'grau';
    $s->parent = NULL;
    $s->d = 0;
    $queue = array($s);
    while (count($queue) > 0) {
      $u = array_shift($queue);
      
      foreach($g->N($u) as $v) {
        if ($v->color == 'weiss') {
          $v->color = 'grau';
          $v->d = $u->d + 1;
          $v->parent = $u;
          $u->childs[] = $v;
          array_push($queue,$v);
        }
      }
      
      $u->color = 'schwarz';
    }
  }
  
  
  /**
   * Der Graph muss aus DependencyVertices bestehen
   *
   * die besonderen Knoten brauchen wir, da wir "hasOutgoingEdges()" nicht ganz easy berechnen können
   * (wir bräuchten die EdgesList flipped)
   *
   * Cormen, Thomas H.; Leiserson, Charles E.; Rivest, Ronald L.; Stein, Clifford (2001), "Section 22.4: Topological sort", Introduction to Algorithms (2nd ed.), MIT Press and McGraw-Hill, pp. 549–552, ISBN 0-262-03293-7.
   * 
   * gibt eine Liste aller Vertices in der topologischen Sortierung zurück
   * die elemente mit "den meisten dependencies" befinden sich hinten in der liste
   * @return array 
   */
  public static function TopologicalSort(Graph $g) {
    $list = array();
    
    if (count($g->V()) == 0) return $list;
    // rufe DFS auf $g auf
    // füge jeden abgearbeiteten Knoten an den Kopf einer Liste ein
    
    $dfsVisit = function (DependencyVertice $vertice) use ($g, &$list, &$dfsVisit) {
      if (!$vertice->isVisited()) {
        $vertice->setVisited(TRUE);
        
        foreach ($g->N($vertice) as $adjacentVertice) {
          $dfsVisit($adjacentVertice);
        }
        array_unshift($list,$vertice);
      }
    };
    
    // wir beginnen nicht mit einem Startknoten sondern mit allen Startknoten, die keine dependencies haben
    $s = array();
    foreach ($g->V() as $vertice) {
      if (count($vertice->getDependencies()) === 0) {
        $s[] = $vertice;
        $dfsVisit($vertice);
      }
    }
    
    if (count($s) === 0) {
      throw new TopologicalSortRuntimeException('Der Graph g kann nicht topologisch sortiert werden. Es wurden keine Startknoten gefunden die überhaupt keine Dependencies haben');
    }
    
    return $list;
  }
}
?>