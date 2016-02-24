<?php
/*
 * E-cidade Software Publico para Gestao Municipal
 * Copyright (C) 2014 DBSeller Servicos de Informatica
 * www.dbseller.com.br
 * e-cidade@dbseller.com.br
 *
 * Este programa e software livre; voce pode redistribui-lo e/ou
 * modifica-lo sob os termos da Licenca Publica Geral GNU, conforme
 * publicada pela Free Software Foundation; tanto a versao 2 da
 * Licenca como (a seu criterio) qualquer versao mais nova.
 *
 * Este programa e distribuido na expectativa de ser util, mas SEM
 * QUALQUER GARANTIA; sem mesmo a garantia implicita de
 * COMERCIALIZACAO ou de ADEQUACAO A QUALQUER PROPOSITO EM
 * PARTICULAR. Consulte a Licenca Publica Geral GNU para obter mais
 * detalhes.
 *
 * Voce deve ter recebido uma copia da Licenca Publica Geral GNU
 * junto com este programa; se nao, escreva para a Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 * 02111-1307, USA.
 *
 * Copia da licenca no diretorio licenca/licenca_en.txt
 * licenca/licenca_pt.txt
 */
class cl_empagedadosretmovocorrenciaobservacao extends DAOBasica {
	public function __construct() {
		parent::__construct ( "plugins.empagedadosretmovocorrenciaobservacao" );
	}
	function sql_query_erro_processamento($e75_codret = null, $campos = "*", $ordem = null, $dbwhere = "") {
		$sql = "select ";
		if ($campos != "*") {
			$campos_sql = split ( "#", $campos );
			$virgula = "";
			for($i = 0; $i < sizeof ( $campos_sql ); $i ++) {
				$sql .= $virgula . $campos_sql [$i];
				$virgula = ",";
			}
		} else {
			$sql .= $campos;
		}
		
		
		$sql .= " from empagedadosret ";
		$sql .= "      inner join empagedadosretmov                              on empagedadosretmov.e76_codret                      = empagedadosret.e75_codret    ";
		$sql .= "      inner join empagedadosretmovocorrencia                    on empagedadosretmovocorrencia.e02_empagedadosret    = empagedadosret.e75_codret    ";
		$sql .= "                                                               and empagedadosretmovocorrencia.e02_empagedadosretmov = empagedadosretmov.e76_codmov ";
		$sql .= "      left  join plugins.empagedadosretmovocorrenciaobservacao  on empagedadosretmovocorrencia                       = e02_sequencial               ";
		$sql .= "      inner join errobanco          on errobanco.e92_sequencia      = empagedadosretmovocorrencia.e02_errobanco ";
		$sql .= "      left  join empageslip         on empageslip.e89_codmov        = empagedadosretmov.e76_codmov    ";
		$sql .= "      left  join slip               on slip.k17_codigo              = empageslip.e89_codigo           ";
		$sql .= "      left  join empord             on empord.e82_codmov            = empagedadosretmov.e76_codmov    ";
		$sql .= "      left  join empagemov          on empord.e82_codmov            = empagemov.e81_codmov            ";
		$sql .= "      left  join pagordem           on pagordem.e50_codord          = empord.e82_codord               ";
		$sql .= "      left  join empempenho         on empempenho.e60_numemp        = pagordem.e50_numemp             ";
		$sql .= "      left  join cgm                on empempenho.e60_numcgm        = cgm.z01_numcgm                  ";
		
		$sql2 = "";
		if ($dbwhere == "") {
			if ($e75_codret != null) {
				$sql2 .= " where empagedadosret.e75_codret = $e75_codret ";
			}
		} else if ($dbwhere != "") {
			$sql2 = " where $dbwhere";
		}
		$sql .= $sql2;
		if ($ordem != null) {
			$sql .= " order by ";
			$campos_sql = split ( "#", $ordem );
			$virgula = "";
			for($i = 0; $i < sizeof ( $campos_sql ); $i ++) {
				$sql .= $virgula . $campos_sql [$i];
				$virgula = ",";
			}
		}
		return $sql;
	}
}
