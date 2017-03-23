<?
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

// MODULO: empenho
// CLASSE DA ENTIDADE criada para ser utilizada por plugin
class cl_PluginGeracaoArquivoOBN {
	// cria variaveis de erro
	var $rotulo = null;
	var $query_sql = null;
	var $numrows = 0;
	var $numrows_incluir = 0;
	var $numrows_alterar = 0;
	var $numrows_excluir = 0;
	var $erro_status = null;
	var $erro_sql = null;
	var $erro_banco = null;
	var $erro_msg = null;
	var $erro_campo = null;
	var $pagina_retorno = null;
	
	// funcao construtor da classe
	function cl_PluginGeracaoArquivoOBN() {
	}
	
	// funcao do recordset
	function sql_record($sql) {
		$result = db_query ( $sql );
		if ($result == false) {
			$this->numrows = 0;
			$this->erro_banco = str_replace ( "\n", "", @pg_last_error () );
			$this->erro_sql = "Erro ao selecionar os registros.";
			$this->erro_msg = "Usuário: \\n\\n " . $this->erro_sql . " \\n\\n";
			$this->erro_msg .= str_replace ( '"', "", str_replace ( "'", "", "Administrador: \\n\\n " . $this->erro_banco . " \\n" ) );
			$this->erro_status = "0";
			return false;
		}
		$this->numrows = pg_numrows ( $result );
		if ($this->numrows == 0) {
			$this->erro_banco = "";
			$this->erro_sql = "Record Vazio";
			$this->erro_msg = "Usuário: \\n\\n " . $this->erro_sql . " \\n\\n";
			$this->erro_msg .= str_replace ( '"', "", str_replace ( "'", "", "Administrador: \\n\\n " . $this->erro_banco . " \\n" ) );
			$this->erro_status = "0";
			return false;
		}
		return $result;
	}
	
	function getSqlDadosMovimentacao($sCodigoGeracao, $iInstituicao, $iAno, $iCodigoMovimento = null) {
		$regerar = "sim";
		$sWhere = "1=1";
		$sInner = "left";
		/*$sWhereArquivo = " e80_instit      = {$iInstituicao}   ";
		$sWhereArquivo .= " and e90_codgera = {$sCodigoGeracao} ";*/
    $sWhereArquivo  = " e90_codgera = {$sCodigoGeracao} ";
		$sWhereArquivo .= " and e90_cancelado = 'false'";
		
		$sWhereMovimento = " e81_codmov = {$iCodigoMovimento} ";
		
		if (! empty ( $sCodigoGeracao )) {
			$sWhere .= "and {$sWhereArquivo}";
			$sInner = "inner";
		}
		
		if (! empty ( $iCodigoMovimento )) {
			$sWhere .= "and {$sWhereMovimento}";
			$regerar = "nao";
		}
		
		$sqlOrdem = "select distinct
             			     o41_orgao::varchar as orgao,
            			     o41_unidade::varchar as unidade,
                                     coalesce(empagemovtipotransmissaofatura.fatura,'f') as comfatura,
                                     e60_codemp||'/'||e60_anousu as empenho,
                        null::integer as slip,
                        e82_codord as ordem,
                        pc63_banco as banco_fornecedor,
	                      e90_codgera,
	                      e81_codmov,
	                      e87_data,
	                      e87_dataproc,
	                      c63_banco,
	                      c63_agencia,
	                      coalesce(c63_dvagencia,'0') as c63_dvagencia,
	                      c63_conta,
	                      coalesce(c63_dvconta,'0') as c63_dvconta,
	                      pc63_agencia::varchar,
                        coalesce(pc63_agencia_dig,'0') as pc63_agencia_dig,
	                      pc63_conta::varchar,
                        coalesce(pc63_conta_dig,'0') as pc63_conta_dig,
                        pc63_codigooperacao::varchar,
                        conplanoconta.c63_codigooperacao::varchar,
                        pc63_tipoconta,
	                      round((e81_valor-(select coalesce(sum(e23_valorretencao),0)::numeric as valorRetido                   
                                                               from retencaoreceitas                                                            
                                                                    inner join retencaopagordem  on e23_retencaopagordem = e20_sequencial       
                                                                    inner join retencaotiporec   on e23_retencaotiporec  = e21_sequencial       
                                                                    inner join pagordemnota      on e20_pagordem         = e71_codord           
                                                                    inner join retencaoempagemov on e27_retencaoreceitas = e23_sequencial       
                                                              where e27_empagemov = e81_codmov                                                 
                                                                and e23_ativo     is true                                                        
                                                                and e27_principal is true)), 2) as valor,
                        round((e81_valor-(select coalesce(sum(e23_valorretencao),0)::numeric as valorRetido                   
                                       from retencaoreceitas                                                            
                                            inner join retencaopagordem  on e23_retencaopagordem = e20_sequencial       
                                            inner join retencaotiporec   on e23_retencaotiporec  = e21_sequencial       
                                            inner join pagordemnota      on e20_pagordem         = e71_codord           
                                            inner join retencaoempagemov on e27_retencaoreceitas = e23_sequencial       
                                      where e27_empagemov = e81_codmov                                                 
                                        and e23_ativo     is true                                                        
                                        and e27_principal is true)), 2) as valorori,
	                      case when  pc63_banco = c63_banco then '01' else '03' end as  lanc,
	                      coalesce(pc63_banco,'000') as pc63_banco,
	                      e83_convenio as convenio,
	                      z01_numcgm as numcgm,
	                      substr(z01_nome,1,40) as z01_nome,
	                      case when trim(pc63_cnpjcpf) = '0' or trim(pc63_cnpjcpf) = '' or pc63_cnpjcpf is null then length(trim(z01_cgccpf)) else length(trim(pc63_cnpjcpf)) end as tam,
	                      case when trim(pc63_cnpjcpf) = '0' or trim(pc63_cnpjcpf) = '' or pc63_cnpjcpf is null then z01_cgccpf else pc63_cnpjcpf end as z01_cgccpf,
	                      e88_codmov as cancelado,
	                      z01_ender,
	                      z01_numero,
	                      z01_compl,
	                      z01_bairro,
	                      z01_munic,
	                      z01_cep,
	                      z01_uf,
	                      fc_validaretencoesmesanterior(e81_codmov,null) as validaretencao,
	                      e83_codigocompromisso,
                        empagemovdetalhetransmissao.*,
                        empagemovpagamento.*,
                        (select e152_finalidadepagamentofundeb from empempenhofinalidadepagamentofundeb where e152_numemp = empempenho.e60_numemp) as finalidadepagamento,
                        exists(select * 
                                 from empagedadosretmov 
                                      inner join empagedadosret on e76_codret = e75_codret 
                                                               and e75_ativo is true
                                where e76_codmov = e81_codmov) as processado,
		        null::integer as slipvinculo,
			empagetipo.e83_conta as saltes_pag,
			'{$regerar}' as regerar
                   from empagemov
            	          {$sInner} join empageconfgera               on e90_codmov = e81_codmov
            	          {$sInner} join empagegera                   on e90_codgera=e87_codgera
                        {$sInner} join empage                       on  empage.e80_codage = empagemov.e81_codage
            	          {$sInner} join empempenho                   on e60_numemp = e81_numemp
             {$sInner} join orcdotacao on e60_coddot = o58_coddot and e60_anousu = o58_anousu
	         {$sInner} join orcunidade on o58_unidade = o41_unidade and o58_orgao = o41_orgao and o58_anousu = o41_anousu
            	          {$sInner} join empagepag                    on e81_codmov = e85_codmov
            	          {$sInner} join empagetipo                   on e85_codtipo = e83_codtipo
              	        {$sInner} join empord                       on empord.e82_codmov         = empagemov.e81_codmov
            	          left      join empageslip                  on e81_codmov = e89_codmov
            	          left      join conplanoreduz               on c61_codcon in (select c61_codcon from conplanoreduz where c61_reduz = e83_conta) and c61_anousu = " . $iAno . "
              	        left      join conplanoconta               on c63_codcon = c61_codcon and c63_anousu = c61_anousu
              	        left      join slip                        on slip.k17_codigo = e89_codigo
              	        left      join slipnum                     on slipnum.k17_codigo = slip.k17_codigo
              	        left      join empageconfcanc              on e88_codmov = e90_codmov
              	        left      join empagemovconta              on e90_codmov = e98_codmov
              	        left      join pcfornecon                  on pc63_contabanco = e98_contabanco
              	        left      join cgm                         on z01_numcgm = pc63_numcgm
              	        left      join empagemovtipotransmissao    on empagemovtipotransmissao.e25_empagemov = empagemov.e81_codmov
              	        left      join (select distinct on (e74_empagemov) *
                                        from empagemovdetalhetransmissao ) 
                                    as empagemovdetalhetransmissao on  empagemovdetalhetransmissao.e74_empagemov = empagemov.e81_codmov
              	        left      join plugins.empagemovtipotransmissaofatura on empagemovtipotransmissaofatura.empagemovtipotransmissao = empagemovtipotransmissao.e25_sequencial
                        left      join (select distinct on (empagemov) *
                                        from plugins.empagemovpagamento  )
                                    as empagemovpagamento on empagemovpagamento.empagemov = empagemov.e81_codmov
      	          where
      	              {$sWhere}";
		// "and empagemovtipotransmissao.e25_empagetipotransmissao = 2";
		
		$sqlSlip = "select
                      distinct
		              db01_orgao::varchar as orgao,
		              db01_unidade::varchar as unidade,
    			      coalesce(empagemovtipotransmissaofatura.fatura,'f') as comfatura,
                      null::varchar as empenho,
                      slip.k17_codigo as slip,
                      null::integer as ordem,
                      pc63_banco as banco_fornecedor,
	                    e90_codgera,
	                    e81_codmov,
	                    e87_data,
	                    e87_dataproc,
	                    conplanoconta.c63_banco,
	                    conplanoconta.c63_agencia,
	                    coalesce(conplanoconta.c63_dvagencia,'0') as c63_dvagencia,
	                    conplanoconta.c63_conta,
	                    coalesce(conplanoconta.c63_dvconta,'0') as c63_dvconta,
                      (case
                          when pc63_agencia is null 
              	              then contadebito.c63_agencia
                          else pc63_agencia
                      end )::varchar as pc63_agencia,

                        coalesce((case when pc63_agencia_dig is null 
              	                 then contadebito.c63_dvagencia
                         else pc63_agencia_dig end ),'0')::varchar as pc63_agencia_dig,
                      (case when pc63_conta is null 
              	                   then contadebito.c63_conta
                            else pc63_conta
                       end)::varchar as pc63_conta,
                        coalesce((case when pc63_conta_dig is null 
              	                         then contadebito.c63_dvconta
                         else pc63_conta_dig end ),'0')::varchar as pc63_conta_dig,
                      (case
                         when pc63_codigooperacao is null 
              	                             then contadebito.c63_codigooperacao
                         else pc63_codigooperacao
                       end )::varchar as pc63_codigooperacao,
                       conplanoconta.c63_codigooperacao::varchar,
                       (case
                         when pc63_tipoconta is null 
                           then contadebito.c63_tipoconta
                         else pc63_tipoconta
                       end ) as pc63_tipoconta,
	                     round((e81_valor-(select coalesce(sum(e23_valorretencao),0)::numeric as valorRetido                   
                                                              from retencaoreceitas                                                            
                                                                   inner join retencaopagordem  on e23_retencaopagordem = e20_sequencial       
                                                                   inner join retencaotiporec   on e23_retencaotiporec  = e21_sequencial       
                                                                   inner join pagordemnota      on e20_pagordem         = e71_codord           
                                                                   inner join retencaoempagemov on e27_retencaoreceitas = e23_sequencial       
                                                             where e27_empagemov = e81_codmov                                                 
                                                               and e23_ativo     is true                                                        
                                                               and e27_principal is true)), 2) as valor,
                       round((e81_valor-(select coalesce(sum(e23_valorretencao),0)::numeric as valorRetido                   
                                      from retencaoreceitas                                                            
                                           inner join retencaopagordem  on e23_retencaopagordem = e20_sequencial       
                                           inner join retencaotiporec   on e23_retencaotiporec  = e21_sequencial       
                                           inner join pagordemnota      on e20_pagordem         = e71_codord           
                                           inner join retencaoempagemov on e27_retencaoreceitas = e23_sequencial       
                                     where e27_empagemov = e81_codmov                                                 
                                       and e23_ativo     is true                                                        
                                       and e27_principal is true)), 2) as valorori,
	                    case
                        when  ((case when pc63_banco is not null then pc63_banco else contadebito.c63_banco end ) = conplanoconta.c63_banco
                                or descrconta.c63_banco = conplanoconta.c63_banco )
                           then '01'
                        else '03'
                      end as  lanc,
                      ( case when pc63_banco is null 
                        then contadebito.c63_banco
                             else pc63_banco
                        end ) as pc63_banco,

	                    e83_convenio as convenio,
	                    case when cgm.z01_numcgm is null then cgmslip.z01_numcgm else cgm.z01_numcgm end as z01_numcgm,
	                    substr(case when cgm.z01_nome is null then cgmslip.z01_nome else cgm.z01_nome end,1,40) as z01_nome,
	                    (case 
                           when trim(pc63_cnpjcpf) = '0' or trim(pc63_cnpjcpf) = '' or pc63_cnpjcpf is null 
                             then length(trim( (case 
                                                  when contadebito.c63_identificador is null 
                                                    then case 
                                                           when cgm.z01_cgccpf is null 
                                                             then cgmslip.z01_cgccpf 
                                                           else cgm.z01_cgccpf 
                                                         end
                                                  else
                                                   contadebito.c63_identificador 
                                                 end)
					                         )
     					                )
	                         else length(trim(pc63_cnpjcpf)) 
                        end) as tam,
	                    (case 
                           when trim(pc63_cnpjcpf) = '0' or trim(pc63_cnpjcpf) = '' or pc63_cnpjcpf is null 
                             then (case 
                                     when contadebito.c63_identificador is null 
                                       then (case 
                                               when cgm.z01_cgccpf is null 
                                                 then cgmslip.z01_cgccpf 
                                               else cgm.z01_cgccpf 
                                             end)
                                       else contadebito.c63_identificador 
                                  end)
                           else pc63_cnpjcpf 
                        end) as z01_cgccpf,

	                    e88_codmov as cancelado,
	                    case when cgm.z01_ender is null then cgmslip.z01_ender else cgm.z01_ender end as z01_ender,
	                    case when cgm.z01_numero is null then cgmslip.z01_numero else cgm.z01_numero end as z01_numero,
	                    case when cgm.z01_compl is null then cgmslip.z01_compl else cgm.z01_compl end as z01_compl,
	                    case when cgm.z01_bairro is null then cgmslip.z01_bairro else cgm.z01_bairro end as z01_bairro,
	                    case when cgm.z01_munic is null then cgmslip.z01_munic else cgm.z01_munic end as z01_munic,
	                    case when cgm.z01_cep is null then cgmslip.z01_cep else cgm.z01_cep end as z01_cep,
	                    case when cgm.z01_uf is null then cgmslip.z01_uf else cgm.z01_uf end as z01_uf,
	                    false as validaretencao,
	                    e83_codigocompromisso,
                      empagemovdetalhetransmissao.*,
                      empagemovpagamento.*,
                      (select e153_finalidadepagamentofundeb from slipfinalidadepagamentofundeb where e153_slip = slip.k17_codigo) as finalidadepagamento,
                      exists(select * 
                                 from empagedadosretmov 
                                      inner join empagedadosret on e76_codret = e75_codret 
                                                               and e75_ativo is true
                                where e76_codmov = e81_codmov) as processado,
		      --sliptipooperacaovinculo.k153_slipoperacaotipo as slipvinculo
                      (case when ar.slip is null then sliptipooperacaovinculo.k153_slipoperacaotipo else 1 end) as slipvinculo,
		      --13 as slipvinculo,
		      conplanoreduz.c61_reduz as saltes_pag,
		      '{$regerar}' as regerar
                  from empagemov
	                    {$sInner} join empageconfgera    on e90_codmov         = e81_codmov
	                    {$sInner} join empagegera        on e90_codgera        = e87_codgera
                      {$sInner} join empage            on  empage.e80_codage = empagemov.e81_codage
	                    {$sInner} join empagepag         on e81_codmov         = e85_codmov
	                    {$sInner} join empagetipo        on e85_codtipo        = e83_codtipo
	                    {$sInner} join empageslip        on e81_codmov         = e89_codmov
	                    {$sInner} join conplanoreduz     on e83_conta          = c61_reduz and c61_anousu = " . $iAno . "
                      {$sInner} join conplanoconta     on c63_codcon         = c61_codcon and c63_anousu = c61_anousu
	                    {$sInner} join slip              on slip.k17_codigo    = e89_codigo
			    {$sInner} join sliptipooperacaovinculo	   on slip.k17_codigo = sliptipooperacaovinculo.k153_slip
	                    {$sInner} join slipnum           on slipnum.k17_codigo = slip.k17_codigo
                 {$sInner} join plugins.slipdepartamento on slipdepartamento.slip = slip.k17_codigo
                 {$sInner} join db_departorg             on db01_coddepto = slipdepartamento.departamento
                                                        and db01_anousu = {$iAno}

	                    
                 {$sInner} join conplanoreduz reduzdebito on reduzdebito.c61_reduz   = k17_debito
                 {$sInner} join conplano      planodebito on planodebito.c60_codcon  = reduzdebito.c61_codcon
                                                         and planodebito.c60_anousu = {$iAno}
                      left join conplanoconta contadebito on contadebito.c63_codcon  = reduzdebito.c61_codcon
                                                          and contadebito.c63_anousu = {$iAno}
                      left join saltes                    on saltes.k13_reduz        = reduzdebito.c61_reduz
                      left join empageconfcanc on e88_codmov   = e90_codmov
                      left join empagemovconta on e90_codmov   = e98_codmov
                      left join pcfornecon on pc63_contabanco  = e98_contabanco
                      left join cgm cgmslip on cgmslip.z01_numcgm = slipnum.k17_numcgm
                      left join cgm on cgm.z01_numcgm              = cgmslip.z01_numcgm
                      left join conplanoreduz cre on cre.c61_reduz   = k17_debito and cre.c61_anousu = " . $iAno . "
  	                  left join conplano concre on concre.c60_codcon = cre.c61_codcon and concre.c60_anousu = cre.c61_anousu
                      left join conplanoconta descrconta on concre.c60_codcon = descrconta.c63_codcon and concre.c60_anousu = descrconta.c63_anousu
                      left join empagemovtipotransmissao on empagemovtipotransmissao.e25_empagemov = empagemov.e81_codmov
                      left join (select  distinct on (e74_empagemov) *
                                 from empagemovdetalhetransmissao )
                                as empagemovdetalhetransmissao on  empagemovdetalhetransmissao.e74_empagemov = empagemov.e81_codmov
                      left join plugins.empagemovtipotransmissaofatura on empagemovtipotransmissaofatura.empagemovtipotransmissao = empagemovtipotransmissao.e25_sequencial
                      left join plugins.autorizacaorepasse ar on slip.k17_codigo = ar.slip
                      left join (select  distinct on (empagemov) * 
                                 from plugins.empagemovpagamento)
                                as empagemovpagamento on empagemovpagamento.empagemov = empagemov.e81_codmov
  	                  where
                        {$sWhere}" . "--order by c63_conta,lanc,e81_codmov";
		
		$sqlMov = $sqlOrdem . " union " . $sqlSlip;
    
		return $sqlMov;
	}
	
	/*
	 * copia do metodo existente na classe cl_empageslip_classe
	 * adicionando ligacoes com outras tabelas
	 */
	public function sql_query_txtbanco($e89_codmov = null, $campos = "*", $ordem = null, $dbwhere = "") {
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
	
		$sql .= " from empageslip ";
		$sql .= "      inner join empagemov                on empagemov.e81_codmov             = empageslip.e89_codmov";
		$sql .= "      inner join empage   b               on b.e80_codage                     = empagemov.e81_codage";
		$sql .= "	   inner join empageconf               on e86_codmov                       = e81_codmov ";
		$sql .= "	   inner join empagemovforma           on e81_codmov                       = e97_codmov ";
		$sql .= "      inner join empagepag                on e85_codmov                       = empagemov.e81_codmov";
		$sql .= "      inner join empagetipo               on empagetipo.e83_codtipo           = empagepag.e85_codtipo";
		$sql .= "      inner join slip s                   on e89_codigo                       = s.k17_codigo";
		$sql .= "      inner join plugins.slipdepartamento on slipdepartamento.slip            = s.k17_codigo                  ";
		$sql .= "      inner join db_departorg             on db_departorg.db01_coddepto       = slipdepartamento.departamento ";
		$sql .= "                                         and db_departorg.db01_anousu         = " . db_getsession ( "DB_anousu" );
		$sql .= "      inner join orcunidade               on orcunidade.o41_unidade           = db_departorg.db01_unidade";
		$sql .= "                                         and orcunidade.o41_orgao             = db_departorg.db01_orgao";
		$sql .= "                                         and orcunidade.o41_anousu            = db_departorg.db01_anousu";
		$sql .= "      inner join orcorgao                 on orcorgao.o40_orgao               = orcunidade.o41_orgao  ";
		$sql .= "                                         and orcorgao.o40_anousu              = orcunidade.o41_anousu ";
		$sql .= "	   inner join conplanoreduz pag        on pag.c61_reduz                    = e83_conta ";
		$sql .= "                                         and pag.c61_anousu                   = " . db_getsession ( "DB_anousu" );
		$sql .= "	   inner join conplano conpag          on conpag.c60_codcon                = pag.c61_codcon ";
		$sql .= "                                         and conpag.c60_anousu                = pag.c61_anousu ";
		$sql .= "	   inner join conplanoconta            on conpag.c60_codcon                = conplanoconta.c63_codcon ";
		$sql .= "                                         and conpag.c60_anousu                = conplanoconta.c63_anousu ";
		$sql .= "      inner join conplanocontabancaria    on conplanocontabancaria.c56_codcon = conplanoconta.c63_codcon ";
		$sql .= "                                         and conplanocontabancaria.c56_anousu = conplanoconta.c63_anousu ";
		$sql .= "      inner join contabancaria            on contabancaria.db83_sequencial    = conplanocontabancaria.c56_contabancaria ";
		$sql .= "	   inner join orctiporec               on pag.c61_codigo                   = o15_codigo  ";
		$sql .= "       left join emphist                  on s.k17_hist                       = e40_codhist ";
		$sql .= "	   inner join slipnum o                on o.k17_codigo                     = s.k17_codigo";
		$sql .= "	    left join cgm                      on z01_numcgm                       = o.k17_numcgm";
		$sql .= "	    left join empageconfgera           on e81_codmov                       = e90_codmov  ";
		$sql .= "       left join empagemovconta           on empagemovconta.e98_codmov        = empagemov.e81_codmov ";
		$sql .= "       left join pcfornecon               on pcfornecon.pc63_contabanco       = empagemovconta.e98_contabanco";
		$sql .= "	    left join conplanoreduz cre        on cre.c61_reduz                    = k17_debito";
		$sql .= "                                         and cre.c61_anousu                   = " . db_getsession ( "DB_anousu" );
		$sql .= "	    left join conplano concre          on concre.c60_codcon                = cre.c61_codcon ";
		$sql .= "                                         and concre.c60_anousu                = cre.c61_anousu ";
		$sql .= "	    left join conplanoconta descrconta on concre.c60_codcon                = descrconta.c63_codcon";
		$sql .= "                                         and concre.c60_anousu                = descrconta.c63_anousu ";
		$sql .= "       left join empagemovtipotransmissao on e25_empagemov                    = empagemov.e81_codmov ";
		$sql2 = "";
		if ($dbwhere == "") {
			if ($e89_codmov != null) {
				$sql2 .= " where empageslip.e89_codmov = $e89_codmov ";
			}
			if ($e89_codigo != null) {
				if ($sql2 != "") {
					$sql2 .= " and ";
				} else {
					$sql2 .= " where ";
				}
				$sql2 .= " empageslip.e89_codigo = $e89_codigo ";
			}
		} else if ($dbwhere != "") {
			$sql2 = " where $dbwhere";
		}
		$sql2 .= ($sql2 != "" ? " and " : " where ") . " k17_instit = " . db_getsession ( "DB_instit" );
        $sql2 .= ($sql2 != "" ? " and " : " where ") . " k17_dtestorno is null ";
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
	
	/*
	 * copia do metodo existente na classe cl_empagemov_classe
	 * adicionando ligacoes com outras tabelas
	 */
	function sql_query_txt($e81_codmov = null, $campos = "*", $ordem = null, $dbwhere = "") {
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
		$sql .= " from empagemov ";
		$sql .= "      inner join empagemovforma           on  empagemovforma.e97_codmov       = empagemov.e81_codmov            ";
		$sql .= "      inner join empage                   on  empage.e80_codage               = empagemov.e81_codage            ";
		$sql .= "      inner join empagepag                on  empagepag.e85_codmov            = empagemov.e81_codmov            ";
		$sql .= "      inner join empagetipo               on  empagetipo.e83_codtipo          = empagepag.e85_codtipo           ";
		$sql .= "      inner join conplanoreduz            on  conplanoreduz.c61_codcon       in (select c61_codcon from conplanoreduz where c61_reduz = empagetipo.e83_conta)";
		$sql .= "                                         and  conplanoreduz.c61_anousu        = " . db_getsession ( "DB_anousu" );
		$sql .= "                                         and  conplanoreduz.c61_instit        = " . db_getsession ( "DB_instit" );
		$sql .= "      inner join conplanoconta            on conplanoconta.c63_codcon         = conplanoreduz.c61_codcon ";
		$sql .= "                                         and conplanoconta.c63_anousu         = conplanoreduz.c61_anousu ";
		$sql .= "      inner join conplanocontabancaria    on conplanocontabancaria.c56_codcon = conplanoconta.c63_codcon ";
		$sql .= "                                         and conplanocontabancaria.c56_anousu = conplanoconta.c63_anousu ";
		$sql .= "      inner join contabancaria            on contabancaria.db83_sequencial    =  conplanocontabancaria.c56_contabancaria ";
		$sql .= "      inner join empageconf               on empageconf.e86_codmov            = empagemov.e81_codmov          ";
		$sql .= "      left  join empageconfgera           on empageconfgera.e90_codmov        = empagemov.e81_codmov          ";
		$sql .= "      inner join empempenho               on empempenho.e60_numemp            = empagemov.e81_numemp          ";
		$sql .= "      inner join orcdotacao               on orcdotacao.o58_coddot            = empempenho.e60_coddot         ";
		$sql .= "                                         and orcdotacao.o58_anousu            = empempenho.e60_anousu         ";
		$sql .= "      inner join orcunidade               on orcunidade.o41_unidade           = orcdotacao.o58_unidade        ";
		$sql .= "                                         and orcunidade.o41_orgao             = orcdotacao.o58_orgao          ";
		$sql .= "                                         and orcunidade.o41_anousu            = orcdotacao.o58_anousu         ";
		$sql .= "      inner join orcorgao                 on orcorgao.o40_orgao               = orcunidade.o41_orgao          ";
		$sql .= "                                         and orcorgao.o40_anousu              = orcunidade.o41_anousu         ";
		$sql .= "      inner join orctiporec               on orctiporec.o15_codigo            = orcdotacao.o58_codigo         ";
		$sql .= "      inner join empord                   on empord.e82_codmov                = empagemov.e81_codmov          ";
		$sql .= "      inner join pagordem                 on pagordem.e50_codord              = empord.e82_codord             ";
		$sql .= "      inner join pagordemele              on pagordem.e50_codord              = pagordemele.e53_codord        ";
		$sql .= "      left  join empagemovconta           on empagemovconta.e98_codmov        = empagemov.e81_codmov          ";
		$sql .= "      left  join pcfornecon               on pcfornecon.pc63_contabanco       = empagemovconta.e98_contabanco ";
		$sql .= "      left  join cgm                      on cgm.z01_numcgm                   = pcfornecon.pc63_numcgm        ";
		$sql .= "      left  join saltes                   on saltes.k13_conta                 = empagetipo.e83_conta          ";
		$sql .= "      left  join empagedadosretmov        on empagedadosretmov.e76_codmov     = empagemov.e81_codmov          ";
		$sql .= "      left  join empagemovtipotransmissao on e25_empagemov                    = empagemov.e81_codmov          ";
	
		$sql2 = "";
		if ($dbwhere == "") {
			if ($e81_codmov != null) {
				$sql2 .= " where empagemov.e81_codmov = $e81_codmov ";
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
	
	/*
	 * Metodo criado para buscar os dados para geracao do arquivo txt
	 * utilizacao da lógica do fonte emp4_empageconfgera001_ordem.php
	 */
	public function sql_buscaMovimentosGeracaoArquivoOBN ($sWhereEmpenho = null, $sWhereSlip = null) {
		
		$oInstit   = db_stdClass::getDadosInstit();
		
		$sSqlEmpenhos    = $this->sql_query_txt(null,"distinct
		                                              o58_orgao   as orgao,
	                                                  o58_unidade as unidade,
		                                              o41_descr,
		                                              e98_contabanco,
                                                      e25_empagetipotransmissao,
                                                      pc63_conta,
                                                      pc63_agencia,
                                                      pc63_banco as banco,
                                                      e80_codage,
                                                      e50_codord,
                                                      e50_data,
                                                      e82_codord,
                                                      o15_codigo,
                                                      o15_descr,
                                                      e81_codmov,
                                                      e83_codtipo,
                                                      e83_descr,
                                                      e60_emiss,
                                                      e60_numemp,
                                                      e60_anousu,
                                                      e60_codemp,
                                                      z01_numcgm,
                                                      z01_nome,
                                                      e81_valor,
                                                      db83_identificador,
                                                      fc_validaretencoesmesanterior(e81_codmov,null) as validaretencao,
                                                      fc_valorretencaomov(e81_codmov,false) as vlrretencao,
                                                      1 as tipo,
				                                      '' as tiposlip",
				                                      "",
				                                      "$sWhereEmpenho");
		
		$sqlSlips  = $this->sql_query_txtbanco(null,"db01_orgao   as orgao,
				                                     db01_unidade as unidade,
				                                     o41_descr,
				                                     e98_contabanco,
				                                     e25_empagetipotransmissao,
				                                     (case when pc63_conta is null then descrconta.c63_conta||'/'||descrconta.c63_dvconta
				                                     else pc63_conta end ) as pc63_conta,
				                                     (case when pc63_agencia is null then descrconta.c63_agencia||'/'||descrconta.c63_dvagencia
				                                     else pc63_agencia end ) as pc63_agencia,
				                                     (case when pc63_banco is null then descrconta.c63_banco
				                                     else pc63_banco end ) as banco,
				                                     e80_codage,
				                                     s.k17_codigo,
				                                     k17_data,
				                                     e89_codigo,
				                                     pag.c61_codigo as o15_codigo,
				                                     orctiporec.o15_descr,
				                                     e81_codmov,
				                                     e83_codtipo,
				                                     e83_descr,
				                                     k17_data,
				                                     0 as e60_numemp,
				                                     '0' as e60_codemp,
				                                     '0'   as e60_anousu,
				                                     (case when z01_numcgm is  not null then z01_numcgm
				                                     else {$oInstit->z01_numcgm} end)  as z01_numcgm,
				                                     (case when z01_nome is  not null then z01_nome
				                                     else '{$oInstit->z01_nome}' end) as z01_nome,
				                                     e81_valor,
				                                     db83_identificador,
				                                     false as validaretencao,
				                                     0 as vlrretencao,
				                                     2 as tipo,
				                                     (select case
				                                               when k153_slipoperacaotipo = 5 then '1'
				                                     		   when k153_slipoperacaotipo = 13 and solicitacaorepasse is null then '2'
				                                     		   when k153_slipoperacaotipo = 13 and solicitacaorepasse is not null then '3'
				                                     		 end
				                                        from sliptipooperacaovinculo
				                                     	     inner join sliptipooperacao on k152_sequencial = k153_slipoperacaotipo
				                                     		  left join plugins.autorizacaorepasse on slip = k153_slip
				                                       where k153_slip = s.k17_codigo) as tiposlip",
				                                     "",
				                                     "{$sWhereSlip}");
		
		$sSqlTxt =  "select * 
		               from ( {$sSqlEmpenhos} 
		                      union 
		                      {$sqlSlips}) as dados 
		              order by orgao, unidade";
		                  
		return $sSqlTxt;
		
	}

}
?>
