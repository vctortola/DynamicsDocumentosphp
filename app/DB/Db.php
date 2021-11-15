<?php

namespace App\DB;

require('Credentials.php');

/**
 * Metodos de acceso a oracle
 */
class Db
{

    /**
     * @var resource The connection resource
     * @access protected
     */
    protected $conn = null;
    /**
     * @var resource The statement resource identifier
     * @access protected
     */
    protected $stid = null;
    /**
     * @var integer The number of rows to prefetch with queries
     * @access protected
     */
    protected $prefetch = 100;

    /**
     * Constructor opens a connection to the database
     */
    function __construct()
    {
        $this->conn = @oci_connect(SCHEMA, PASSWORD, DATABASE, CHARSET);
        if (!$this->conn) {
            $m = oci_error();
            throw new \Exception('Cannot connect to database: ' . $m['message']);
        }
        // Record the "name" of the web user, the client info and the module.
        // These are used for end-to-end tracing in the DB.
        oci_set_client_info($this->conn, CLIENT_INFO);
    }

    /**
     * Destructor closes the statement and connection
     */
    function __destruct()
    {
        if ($this->stid)
            oci_free_statement($this->stid);
        if ($this->conn)
            oci_close($this->conn);
    }

    /**
     * Run a SQL or PL/SQL statement
     *
     * Call like:
     *     Db::execute("insert into mytab values (:c1, :c2)",
     *                 "Insert data", array(array(":c1", $c1, -1),
     *                                      array(":c2", $c2, -1)))
     *
     * For returned bind values:
     *     Db::execute("begin :r := myfunc(:p); end",
     *                 "Call func", array(array(":r", &$r, 20),
     *                                    array(":p", $p, -1)))
     *
     * Note: this performs a commit.
     *
     * @param string $sql The statement to run
     * @param string $action Action text for End-to-End Application Tracing
     * @param array $bindvars Binds. An array of (bv_name, php_variable, length)
     */
    public function ExecuteNonQuery($sql, $action, $bindvars = array())
    {
        $this->stid = oci_parse($this->conn, $sql);
        if ($this->prefetch >= 0) {
            oci_set_prefetch($this->stid, $this->prefetch);
        }
        foreach ($bindvars as $bv) {
            // oci_bind_by_name(resource, bv_name, php_variable, length)
            oci_bind_by_name($this->stid, $bv[0], $bv[1], $bv[2]);
        }
        oci_set_action($this->conn, $action);
        oci_execute($this->stid, OCI_NO_AUTO_COMMIT);
    }

    /**
     * Ejecuta un query y retorna todas las filas.
     *
     * @param string $sql El query que se ejecutara (Ej.: "select sysdate from dual")
     * @param string $action Texto de Accion para End-to-End Application Tracing
     * @param array $bindvars recibe un array de forma (bv_name, php_variable, length)
     * @return array An array of rows
     */
    public function ExecuteNonQueryFetchAll($sql, $bindvars = array())
    {
        $this->ExecuteNonQuery($sql, "Execute Query", $bindvars);
        oci_fetch_all($this->stid, $res, 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
        $this->stid = null;  // free the statement resource
        return ($res);
    }



    /**
     * Llama a los procedimientos que tienen la estructura de core
     * obtieniendo el RETORNO_FUNCION y el PSQLCODE como variables de retorno
     *
     *
     * @param string $NameFunction nombre del procedimiento a ejecutar
     * @param array $params es un array multidimensional que contiene los parametros
     * generales y los parametros del procedimiento.
     * @return array Retorna un array con el valor de PSQLCODE y RETVAL
     */
    public function NumberFunctionNormal(
        $NameFunction,
        $params
    ) {
        try {
            $params_values = [];
            $params_valuesprocedimiento = [];
            $params_marksgenerales = [];
            $params_marksprocedimiento = [];
            $marksgenerales = "";
            $marksprocedimiento = "";
            $i = 0;
            $params_out = [];

            if (count($params[0]) > 0) {
                $j = 0;
                foreach ($params[0] as $key => $value) {

                    $name  = key($params[0][$key]);
                    $valor = $value[$name];
                    $tipodato  = $value["TIPODATO"];
                    $tipo      = $value["TIPO"];
                    $params_marksprocedimiento[$key] = ":" . $name;
                    $params_valuesprocedimiento[$name] = ["VALOR" => $valor, "TIPODATO" => $tipodato, "TIPO" => $tipo];

                    if($tipo == "OUT"){
                        $params_out[$name] = $tipodato;
                    }

                    $j++;
                }

                $marksprocedimiento = implode(",", $params_marksprocedimiento);

                $sql = 'BEGIN :r := ' . $NameFunction . '(' . $marksprocedimiento . '); END;';
            } else {
                $sql = 'BEGIN :r := ' . $NameFunction . '(); END;';
            }

            //dd( $sql );

            $stid = oci_parse($this->conn, "ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD\"T\"HH24:MI:SS'");
            oci_execute($stid);

            $this->stid = oci_parse($this->conn, $sql);

            //dd($params_valuesprocedimiento);
            foreach ($params_valuesprocedimiento as $key => $value) {
                if($value["TIPO"] == "IN"){
                    if($value["TIPODATO"] == "ARRAYVARCHAR2"){
                        $array = $value["VALOR"];
                        $arrayLength = Count($array) > 0 ? Count($array) : 1;
                        $maxLength = Count($array) == 0 ? 4000 : -1;

                        oci_bind_array_by_name($this->stid, ":".$key, $array, $arrayLength, $maxLength, SQLT_CHR);
                    }else{
                        oci_bind_by_name($this->stid, ":".$key, $value["VALOR"]);
                    }
                }
            }

            $arrayret = [];
            $cursorret = [];
            foreach ($params_out as $key => $value) {
                if($value == "CURSOR"){
                  $retorno_funcion2 = oci_new_cursor($this->conn);
                  $cursorret[$key] = $retorno_funcion2;

                  oci_bind_by_name($this->stid, ":".$key, $cursorret[$key], -1, OCI_B_CURSOR);

                }else{
                  oci_bind_by_name($this->stid, ":".$key, $arrayret[$key], 4000, $this->ObtenerTipo($value));
                }
            }

            oci_bind_by_name($this->stid, ":r", $retorno_funcion, $this->ObtenerLength(SQLT_CHR), SQLT_CHR);

            oci_set_action($this->conn, 'EXECUTE ' . $NameFunction);
            oci_execute($this->stid);
            $this->stid = null;

            foreach ($cursorret as $key => $value) {

                oci_execute($cursorret[$key]);
                $resultado = [];
                if ($this->conn) {
                    while (($row = oci_fetch_array($cursorret[$key], OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
                        array_push($resultado, $row);
                    }
                }

                $arrayret[$key] = $resultado;
            }

            $arrayret["RETORNO_FUNCION"] = $retorno_funcion;

            return $arrayret;
        } catch (\Throwable $th) {
            return array("ERROR" => $th->getMessage());
        }
    }

    /**
     * Llama a los procedimientos que tienen la estructura de core
     * obtieniendo el SYS_REFCURSOR y el PSQLCODE como variables de retorno
     *
     *
     * @param string $NameProcedure nombre del procedimiento a ejecutar
     * @param array $params es un array multidimensional que contiene los parametros
     * generales y los parametros del procedimiento.
     * @return array Retorna un array con el valor de PSQLCODE y RETVAL
     */
    public function CursorProcedureNormal(
        $NameProcedure,
        $params
    ) {
        try {
            $params_values = [];
            $params_valuesprocedimiento = [];
            $params_marksgenerales = [];
            $params_marksprocedimiento = [];
            $marksgenerales = "";
            $marksprocedimiento = "";
            $i = 0;
            $params_out = [];

            if (count($params[0]) > 0) {
                $j = 0;
                foreach ($params[0] as $key => $value) {

                    $name  = key($params[0][$key]);
                    $valor = $value[$name];
                    $tipodato  = $value["TIPODATO"];
                    $tipo      = $value["TIPO"];
                    $params_marksprocedimiento[$key] = ":" . $name;
                    $params_valuesprocedimiento[$name] = ["VALOR" => $valor, "TIPODATO" => $tipodato, "TIPO" => $tipo];

                    if($tipo == "OUT" || $tipo == "IN/OUT"){
                        $params_out[$name] = $tipodato;
                    }

                    $j++;
                }

                $marksprocedimiento = implode(",", $params_marksprocedimiento);

                $sql = 'BEGIN ' . $NameProcedure . '(' . $marksprocedimiento . '); END;';
            } else {
                $sql = 'BEGIN ' . $NameProcedure . '(); END;';
            }

            $stid = oci_parse($this->conn, "ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD\"T\"HH24:MI:SS'");
            oci_execute($stid);

            $this->stid = oci_parse($this->conn, $sql);

            foreach ($params_valuesprocedimiento as $key => $value) {
                if($value["TIPO"] == "IN" || $value["TIPO"] == "IN/OUT"){
                    if($value["TIPODATO"] == "ARRAYVARCHAR2"){
                        $array = $value["VALOR"];
                        $arrayLength = Count($array) > 0 ? Count($array) : 1;
                        $maxLength = Count($array) == 0 ? 4000 : -1;

                        oci_bind_array_by_name($this->stid, ":".$key, $array, $arrayLength, $maxLength, SQLT_CHR);
                    }else{
                        oci_bind_by_name($this->stid, ":".$key, $value["VALOR"]);
                    }
                }
            }

            $arrayret = [];
            $cursorret = [];
            foreach ($params_out as $key => $value) {
                if($value == "CURSOR"){
                  $retorno_funcion2 = oci_new_cursor($this->conn);
                  $cursorret[$key] = $retorno_funcion2;

                  oci_bind_by_name($this->stid, ":".$key, $cursorret[$key], -1, OCI_B_CURSOR);

                }else{
                  oci_bind_by_name($this->stid, ":".$key, $arrayret[$key], 4000, $this->ObtenerTipo($value));
                }
            }

            oci_set_action($this->conn, 'EXECUTE ' . $NameProcedure);
            oci_execute($this->stid);
            //oci_execute($rc);

            $this->stid = null;

            foreach ($cursorret as $key => $value) {

                oci_execute($cursorret[$key]);
                $resultado = [];
                if ($this->conn) {
                    while (($row = oci_fetch_array($cursorret[$key], OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
                        array_push($resultado, $row);
                    }
                }

                $arrayret[$key] = $resultado;
            }

            return $arrayret;
        } catch (\Throwable $th) {
            return array("ERROR" => $th->getMessage());
        }
    }

    /**
   * Llama a los procedimientos que tienen la estructura de core
   * obtieniendo el RETORNO_FUNCION y el PSQLCODE como variables de retorno
   *
   *
   * @param string $NameFunction nombre del procedimiento a ejecutar
   * @param array $params es un array multidimensional que contiene los parametros
   * generales y los parametros del procedimiento.
   * @return array Retorna un array con el valor de PSQLCODE y RETVAL
   */
    public function CursorFunctionNormal(
        $NameFunction,
        $params
    ) {
        try {
            $params_values = [];
            $params_valuesprocedimiento = [];
            $params_marksgenerales = [];
            $params_marksprocedimiento = [];
            $marksgenerales = "";
            $marksprocedimiento = "";
            $i = 0;
            $params_out = [];

            if (count($params[0]) > 0) {
                $j = 0;
                foreach ($params[0] as $key => $value) {

                    $name  = key($params[0][$key]);
                    $valor = $value[$name];
                    $tipodato  = $value["TIPODATO"];
                    $tipo      = $value["TIPO"];
                    $params_marksprocedimiento[$key] = ":" . $name;
                    $params_valuesprocedimiento[$name] = ["VALOR" => $valor, "TIPODATO" => $tipodato, "TIPO" => $tipo];

                    if($tipo == "OUT" || $tipo == "IN/OUT"){
                        $params_out[$name] = $tipodato;
                    }

                    $j++;
                }

                $marksprocedimiento = implode(",", $params_marksprocedimiento);

                $sql = 'BEGIN :r := ' . $NameFunction . '( ' . $marksprocedimiento . '); END;';
            } else {
                $sql = 'BEGIN :r := ' . $NameFunction . '(); END;';
            }

            $stid = oci_parse($this->conn, "ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD\"T\"HH24:MI:SS'");
            oci_execute($stid);

            $this->stid = oci_parse($this->conn, $sql);

            foreach ($params_valuesprocedimiento as $key => $value) {
                if($value["TIPO"] == "IN"){
                    if($value["TIPODATO"] == "ARRAYVARCHAR2"){
                        $array = $value["VALOR"];
                        $arrayLength = Count($array) > 0 ? Count($array) : 1;
                        $maxLength = Count($array) == 0 ? 4000 : -1;

                        oci_bind_array_by_name($this->stid, ":".$key, $array, $arrayLength, $maxLength, SQLT_CHR);
                    }else{
                        oci_bind_by_name($this->stid, ":".$key, $value["VALOR"]);
                    }
                }
            }

            $arrayret = [];
            $cursorret = [];
            foreach ($params_out as $key => $value) {
                if($value == "CURSOR"){
                  $retorno_funcion2 = oci_new_cursor($this->conn);
                  $cursorret[$key] = $retorno_funcion2;

                  oci_bind_by_name($this->stid, ":".$key, $cursorret[$key], -1, OCI_B_CURSOR);

                }else{
                  oci_bind_by_name($this->stid, ":".$key, $arrayret[$key], 4000, $this->ObtenerTipo($value));
                }
            }

            $retorno_funcion = oci_new_cursor($this->conn);
            oci_bind_by_name($this->stid, ":r", $retorno_funcion, -1, OCI_B_CURSOR);

            oci_set_action($this->conn, 'EXECUTE ' . $NameFunction);
            oci_execute($this->stid);
            $this->stid = null;

            foreach ($cursorret as $key => $value) {

                oci_execute($cursorret[$key]);
                $resultado = [];
                if ($this->conn) {
                    while (($row = oci_fetch_array($cursorret[$key], OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
                        array_push($resultado, $row);
                    }
                }

                $arrayret[$key] = $resultado;
            }

            $resultado = [];
            oci_execute($retorno_funcion);
            if ($this->conn) {
                while (($row = oci_fetch_array($retorno_funcion, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
                    array_push($resultado, $row);
                }
            }
            $arrayret["RETORNO_FUNCION"] = $resultado;

            return array("RETORNO_FUNCION" => $arrayret);
        } catch (\Throwable $th) {
            return array("ERROR" => $th->getMessage());
        }
    }

    /**
     * Llama a los procedimientos que tienen la estructura de core
     * obtieniendo el SYS_REFCURSOR y el PSQLCODE como variables de retorno
     *
     *
     * @param string $NameProcedure nombre del procedimiento a ejecutar
     * @param array $params es un array multidimensional que contiene los parametros
     * generales y los parametros del procedimiento.
     * @return array Retorna un array con el valor de PSQLCODE y RETVAL
     */
    public function Excecute(
        $NameProcedure,
        $params
    ) {
        try {
            $params_values = [];
            $params_valuesprocedimiento = [];
            $params_marksgenerales = [];
            $params_marksprocedimiento = [];
            $marksgenerales = "";
            $marksprocedimiento = "";
            $i = 0;

            if (count($params[0]) > 0) {
                $j = 0;
                foreach ($params[0] as $key => $value) {
                    $params_marksprocedimiento[$j] = ":" . $key;
                    $params_valuesprocedimiento[$key] = $value;
                    $j++;
                }

                $marksprocedimiento = implode(",", $params_marksprocedimiento);

                $sql = 'BEGIN ' . $NameProcedure . '(' . $marksprocedimiento . '); END;';
            } else {
                $sql = 'BEGIN ' . $NameProcedure . '(); END;';
            }

            //dd($sql);

            $stid = oci_parse($this->conn, "ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD\"T\"HH24:MI:SS'");
            oci_execute($stid);

            $this->stid = oci_parse($this->conn, $sql);

            foreach ($params_values as $key => $value) {
                oci_bind_by_name($this->stid, $key, $params_values[$key]);
            }
            //oci_bind_by_name($this->stid, ":psqlcode", $psqlcode, 80, SQLT_CHR);
            foreach ($params_valuesprocedimiento as $key => $value) {
                oci_bind_by_name($this->stid, $key, $params_valuesprocedimiento[$key]);
            }
            //$rc = oci_new_cursor($this->conn);
            //oci_bind_by_name($this->stid, ":rc", $rc, -1, OCI_B_CURSOR);

            oci_set_action($this->conn, 'EXECUTE ' . $NameProcedure);
            oci_execute($this->stid);
            //oci_execute($rc);
            /*$resultado = [];
            if ($this->conn) {
                while (($row = oci_fetch_array($rc, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
                    array_push($resultado, $row);
                }
            }*/
            $this->stid = null;
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }


        /**
     * Llama a los procedimientos que tienen la estructura de core
     * obtieniendo el RETORNO_FUNCION y el PSQLCODE como variables de retorno
     *
     *
     * @param string $NameFunction nombre del procedimiento a ejecutar
     * @param array $params es un array multidimensional que contiene los parametros
     * generales y los parametros del procedimiento.
     * @return array Retorna un array con el valor de PSQLCODE y RETVAL
     */
    public function StringFunctionNormal(
        $NameFunction,
        $params
    ) {
        try {
            $params_values = [];
            $params_valuesprocedimiento = [];
            $params_marksgenerales = [];
            $params_marksprocedimiento = [];
            $marksgenerales = "";
            $marksprocedimiento = "";
            $params_out = [];

            $i = 0;

            if (count($params[0]) > 0) {
                $j = 0;
                foreach ($params[0] as $key => $value) {

                    $name  = key($params[0][$key]);
                    $valor = $value[$name];
                    $tipodato  = $value["TIPODATO"];
                    $tipo      = $value["TIPO"];
                    $params_marksprocedimiento[$key] = ":" . $name;
                    $params_valuesprocedimiento[$name] = ["VALOR" => $valor, "TIPODATO" => $tipodato, "TIPO" => $tipo];

                    if($tipo == "OUT"){
                        $params_out[$name] = $tipodato;
                    }

                    $j++;
                }

                $marksprocedimiento = implode(",", $params_marksprocedimiento);

                $sql = 'BEGIN :r := ' . $NameFunction . '('  . $marksprocedimiento . '); END;';
            } else {
                $sql = 'BEGIN :r := ' . $NameFunction . '(' . $marksgenerales . '); END;';
            }

            //dd($sql);
            $stid = oci_parse($this->conn, "ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD\"T\"HH24:MI:SS'");
            oci_execute($stid);

            $this->stid = oci_parse($this->conn, $sql);

            foreach ($params_valuesprocedimiento as $key => $value) {
                if($value["TIPO"] == "IN"){
                    if($value["TIPODATO"] == "ARRAYVARCHAR2"){
                        $array = $value["VALOR"];
                        $arrayLength = Count($array) > 0 ? Count($array) : 1;
                        $maxLength = Count($array) == 0 ? 4000 : -1;

                        oci_bind_array_by_name($this->stid, ":".$key, $array, $arrayLength, $maxLength, SQLT_CHR);
                    }else{
                        oci_bind_by_name($this->stid, ":".$key, $value["VALOR"]);
                    }
                }
            }

            $arrayret = [];
            $cursorret = [];
            foreach ($params_out as $key => $value) {
                if($value == "CURSOR"){
                  $retorno_funcion2 = oci_new_cursor($this->conn);
                  $cursorret[$key] = $retorno_funcion2;

                  oci_bind_by_name($this->stid, ":".$key, $cursorret[$key], -1, OCI_B_CURSOR);

                }else{
                  oci_bind_by_name($this->stid, ":".$key, $arrayret[$key], 4000, $this->ObtenerTipo($value));
                }
            }

            //dd(Count($cursorret));

            oci_bind_by_name($this->stid, ":r", $retorno_funcion, 4000, SQLT_CHR);

            oci_set_action($this->conn, 'EXECUTE ' . $NameFunction);
            oci_execute($this->stid);
            $this->stid = null;

            foreach ($cursorret as $key => $value) {

              oci_execute($cursorret[$key]);
              $resultado = [];
              if ($this->conn) {
                  while (($row = oci_fetch_array($cursorret[$key], OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
                      array_push($resultado, $row);
                  }
              }

              $arrayret[$key] = $resultado;
            }

            return array("RETORNO_FUNCION" => $retorno_funcion, "ARRAY_RET" => $arrayret);

        } catch (\Throwable $th) {
            return array("ERROR" => $th->getMessage());
        }
    }

    function ObtenerTipo($tipo)
	{
		switch ($tipo) {
			case 'NUMBER':
				return SQLT_CHR;
			case 'VARCHAR2':
			case 'NVARCHAR2':
			case 'DATE':
			case 'CHAR':
				return SQLT_CHR;
			case 'BLOB':
				return SQLT_BLOB;
			case 'CLOB':
				return SQLT_CLOB;
			case 'LONG RAW':
				return SQLT_LBI;
			case 'PL/SQL BOOLEAN':
				throw new CoreException('Tipo BOOLEAN no soportado');
			case 'PL/SQL RECORD':
				throw new CoreException('OCI no soporta el tipo PL/SQL RECORD');
				break;
			case 'PL/SQL TABLE':
				return SQLT_CHR;
			case 'RAW':
				return SQLT_CHR;
			case 'ROW ID':
				return OCI_B_ROWID;
			case 'TABLE':
				throw new CoreException('Tipo TABLE no soportado todavía');
				break;
			case 'REF CURSOR':
				return SQLT_RSET;
			default:
				return null;
		}
		return null;
    }


    function ObtenerLength($tipo)
	{
		if ($tipo == SQLT_CLOB) {
			return -1;
		}

		if ($tipo == SQLT_CHR) {
			return 4000;
		}

		return -1;
	}


}
