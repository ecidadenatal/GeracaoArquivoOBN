create table plugins.empagemovtipotransmissaofatura (sequencial integer, 
                                                     empagemovtipotransmissao integer, 
                                                     fatura char(1));
create sequence plugins.empagemovtipotransmissaofatura_sequencial_seq;                                                     
                                                     
create table plugins.arquivoobngerado (sequencial integer,
                                       codgera integer,  
                                       datagera date);
create sequence plugins.arquivoobngerado_sequencial_seq;                                        

create table plugins.empagedadosretarquivo (sequencial     integer ,
                                            empagedadosret integer ,
                                            usuario        integer ,
                                            data           date    ,
                                            arquivo        text    );
create sequence plugins.empagedadosretarquivo_sequencial_seq;                                            
                                            
create table plugins.empagedadosretmovocorrenciaobservacao ( sequencial integer,                
                                                             empagedadosretmovocorrencia integer,
                                                             observacao text);
create sequence plugins.empagedadosretmovocorrenciaobservacao_sequencial_seq;          

--insert into configuracoes.db_itensmenu (id_item   ,
--                          descricao ,
--                          help      ,
--                          funcao    ,
--                          itemativo ,
--                          manutencao,
--                          desctec   ,
--                          libcliente)
--                  values (nextval('configuracoes.db_itensmenu_id_item_seq'), 
--                          'Gerar Arquivo TXT - Unidades', 
--                          'Geração de Arquivo TXT por Unidades - plugin', 
--                          'cai4_emissaoarquivoobn001.php', 
--                          1, 
--                          1, 
--                          'Plugin: GeracaoArquivoOBN', 
--                          true);

--insert into configuracoes.db_menu ( id_item, 
--                      id_item_filho, 
--                      menusequencia, 
--                      modulo ) 
--             values (4343, 
--                     currval('configuracoes.db_itensmenu_id_item_seq'), 
--                     18, 
--                     39);
                                                             
