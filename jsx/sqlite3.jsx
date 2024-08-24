const {Link,Dialog,Button,Box,Tab,Tabs,Table, TableHead, TableRow, TableCell, TableBody,TablePagination,Paper,TextField, Input} = MaterialUI;

const {useState,useEffect, useContext, createContext} = React;

const Context = createContext({});
var require;

window.onload = function(){
    ReactDOM.render(<Welcome />, document.getElementById("root"));
}

function Welcome(){
    const [open,setOpen] = useState({
        open:false,
        newDatabase:false
    });
    const [active,setActive] = useState({
        id:0
    })
    const [databases,setDatabses] = useState([]);
    const [stage,setStage] = useState("welcome");

    const getDatabases = () => {
        $.get("api/", {getDatabases:"true"}, function(res){
            setDatabses(res);
        })
    }

    useEffect(()=>{
        getDatabases();
    }, []);

    return (
        <Context.Provider value={{active,setActive,stage,setStage}}>
            <div className="w3-row">
                <div className="w3-col m2 w3-border-right" style={{height:window.innerHeight+"px",overflow:"auto"}}>
                    <div className="pt-20 pb-20 w3-center">
                        <Link href="#" sx={{pl:2,pr:2}}>New</Link>
                        <Link href="#" sx={{pl:2,pr:2}}>Upload</Link>
                        <Link href="#" sx={{pl:2,pr:2}} onClick={e=>setOpen({...open, open:true})}>Open</Link>
                    </div>
                    <div className="">
                        {databases.map((row,index)=>(
                            <div id={row.id} key={row.id} className="w3-padding w3-hover-light-grey pointer" onClick={e=>{
                                setActive(row);
                                setStage("database");
                            }}>
                                <font className="block">{row.name}</font>
                                <font className="block w3-opacity w3-small">{row.dir}</font>
                            </div>
                        ))}
                    </div>
                </div>
                <div className="w3-col m10" style={{height:window.innerHeight+"px",overflow:"auto"}}>
                    {stage == "welcome" ? <></>:
                    stage == "database" ? <DataBase />:
                    stage == "table" ? <TableView />:
                    ""}
                </div>
            </div>

            {open.open ? <OpenDatabase onClose={()=>setOpen({...open, open:false})} onSuccess={()=>{
                setOpen({...open, open:false})
                getDatabases();
            }} />:""}
        </Context.Provider>
    )
}

function OpenDatabase(props){
    const [open,setOpen] = useState(true);
    const [dir, setDir] = useState("../");
    const [currentDir, setCurrentDir] = useState("../");
    const [data,setData] = useState([]);

    const getData = (dir) => {
        $.get("api/", {getData:dir}, function(res){
            setCurrentDir(dir)
            setData(res);
        })
    }

    useEffect(()=>{
        getData(dir);
    }, []);

    const openThis = (row) => {
        if(row.type == "dir"){
            setDir(dir+row.name+"/");
            getData(dir+row.name+"/")
        }
        else{
            $.post("api/", {saveData:dir,file:row.name}, function(res){
                Toast(res);
                setOpen(false);
                props.onClose();
            })
        }
    }

    return (
        <>
            <Dialog open={open} onClose={()=>{
                setOpen(false);
                props.onClose();
            }}>
                <div className="w3-padding-large" style={{width:"400px"}}>
                    <font className="w3-large">Open Database</font>
                    <div style={{maxHeight:"600px",overflow:"auto"}}>
                        {data.map((row,index)=>(
                            <div className="w3-padding w3-hover-light-grey pointer" onClick={e=>openThis(row)}>
                                {row.type == "dir" ? <>
                                <span style={{width:"30px"}}>
                                    <i className="fa fa-folder text-warning"/>
                                </span>
                                <font className="">{row.name}</font>
                                </>:
                                <>
                                <span style={{width:"30px"}}>
                                    <i className="fa fa-file text-secondary"/>
                                </span>
                                <font className="">{row.name}</font>
                                </>}
                            </div>
                        ))}
                    </div>
                    <div className="pt-10 pb-10 clearfix">
                        <Button variant="contained" className="float-right" color="error" onClick={e=>{
                            setOpen(false);
                            props.onClose();
                        }}>Close</Button>
                    </div>
                </div>
            </Dialog>
        </>
    )
}

function DataBase(){
    const {active,setActive} = useContext(Context);

    const [value, setValue] = React.useState(0);
    
    const handleChange = (event, newValue) => {
        setValue(newValue);
    };

    return (
        <>
            <div className="w3-padding bg-secondary w3-text-white">
                {active.name+"("+active.dir+")"}
            </div>
            <Box sx={{ width: '100%', typography: 'body1' }}>
                <Box sx={{ borderBottom: 1, borderColor: 'divider' }}>
                    <Tabs value={value} onChange={handleChange} aria-label="lab API tabs example">
                        <Tab label="Structure" {...a11yProps(0)} style={{textTransform:"none"}} />
                        <Tab label="Sql" {...a11yProps(1)} style={{textTransform:"none"}} />
                        <Tab label="Export" {...a11yProps(2)} style={{textTransform:"none"}} />
                        <Tab label="Import" {...a11yProps(3)} style={{textTransform:"none"}} />
                        <Tab label="Operations" {...a11yProps(4)} style={{textTransform:"none"}} />
                    </Tabs>
                </Box>
                <TabPanel value={value} index={0}>
                    <DatabaseStructure />
                </TabPanel>
                <TabPanel value={value} index={1}>
                    <h3>Accidents Reports</h3>
                </TabPanel>
                <TabPanel value={value} index={2}>
                    <h3>Travel</h3>
                </TabPanel>
                <TabPanel value={value} index={3}>
                    <h3>Supervisor Inspections</h3>
                </TabPanel>
                <TabPanel value={value} index={4}>
                    <h3>Supervisor Inspections</h3>
                </TabPanel>
            </Box>
        </>
    )
}

function DatabaseStructure(){
    const {active,setActive,stage,setStage} = useContext(Context);
    const [tables, setTables] = useState([]);
    const [open,setOpen] = useState({
        add:false
    });
    const [cols,setCols] = useState([]);

    const getTables = () => {
        $.get("api/", {getTables:active.dir,name:active.name}, function(res){
            setTables(res);
        })
    }

    useEffect(()=>{
        getTables();
    }, []);

    useEffect(()=>{
        getTables();
    }, [active]);

    const addCol = () => {
        let last = cols[cols.length - 1];
        setCols([...cols, last+1]);
    }

    const createTable = (event) => {
        event.preventDefault();

        $.post("api/", $(event.target).serialize(), function(response){
            try{
                let res = JSON.parse(response);
                if(res.status){
                    setOpen({...open, add:false});
                    setCols([1]);
                    getTables();
                    Toast("Table Created");
                }
                else{
                    Toast(res.message);
                }
            }
            catch(E){
                alert(E.toString()+response);
            }
        })
    }

    return (
        <>
            <div className="w3-padding">
                <Button variant="contained" size="small" onClick={e=>{
                    setCols([1]);
                    setOpen({...open, add:true})
                }}>Create table</Button>
                <Table sx={{width:500, mt:2}}>
                    <TableHead>
                        <TableRow>
                            <TableCell></TableCell>
                            <TableCell>Table</TableCell>
                            <TableCell>Actions</TableCell>
                            <TableCell>Rows</TableCell>
                        </TableRow>
                    </TableHead>
                    <TableBody>
                        {tables.map((row,index)=>(
                            <TableRow key={index}>
                                <TableCell>
                                    <input type="checkbox"/>
                                </TableCell>
                                <TableCell>
                                    <Link href="#" sx={{fontWeight:"bold"}} onClick={e=>{
                                        setActive({...active, table:row.name});
                                        setStage("table");
                                    }}>{row.name}</Link>
                                </TableCell>
                                <TableCell>
                                    <Link sx={{pr:2}} href="#">Browse</Link>
                                    <Link sx={{pr:2}} href="#">Structure</Link>
                                    <Link sx={{pr:2}} href="#">Empty</Link>
                                    <Link sx={{pr:2}} href="#">Drop</Link>
                                </TableCell>
                                <TableCell>Rows</TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            </div>

            <Dialog open={open.add} onClose={()=>{
                //setCols([1]);
                setOpen({...open, add:false})
            }}>
                <div className="w3-padding" style={{width:"500px"}}>
                    <font>Create table</font>

                    <form className="pt-20 pb-20" onSubmit={createTable}>
                        <input type="hidden" name="dir" value={active.dir}/>
                        <input type="hidden" name="db" value={active.name}/>
                        <input type="hidden" name="count" value={cols[cols.length - 1]}/>
                        <TextField fullWidth label="Table Name" size="small" name="new_table" sx={{mb:2}} />
                        <Button  onClick={addCol}>Add Column</Button>
                        <div className="">
                            <table style={{width:"100%"}}>
                                <thead>
                                    <tr>
                                        <td style={{width:"40$"}}>Name</td>
                                        <td>Type</td>
                                        <td>Primary</td>
                                        <td>AI</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    {cols.map((row,index)=>(
                                        <tr key={index}>
                                            <td style={{width:"40$"}}>
                                                <Input placeholder={"Column "+row} name={"col_"+index} />
                                            </td>
                                            <td>
                                                <select name={"type_"+index}>
                                                    <option>INTEGER</option>
                                                    <option>TEXT</option>
                                                    <option>REAL</option>
                                                    <option>NUMERIC</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="checkbox" name={"primary_"+index} />
                                            </td>
                                            <td>
                                                <input type="checkbox" name={"ai_"+index} />
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        <Button type="submit" variant="contained" sx={{pl:5,pr:5,mt:3}}>Create</Button>
                    </form>

                    <div className="pt-10 clearfix pb-10">
                        <Button variant="contained" color="error" className="float-right" onClick={()=>setOpen({...open, add:false})}>Close</Button>
                    </div>
                </div>
            </Dialog>
        </>
    )
}

function TabPanel(props) {
    const {children, value, index, ...other} = props;

    return (
        <div role="tabpanel" hidden={value !== index} id={`simple-tabpanel-${index}`} aria-labelledby={`simple-tab-${index}`} {...other}>
            {value === index && (
                <Box>
                    {children}
                </Box>
            )}
        </div>
    );
}


function a11yProps(index) {
    return {
        id: `simple-tab-${index}`,
        'aria-controls': `simple-tabpanel-${index}`,
    };
}

function TableView(){
    const {active,setActive} = useContext(Context);

    const [value, setValue] = React.useState(0);
    
    const handleChange = (event, newValue) => {
        setValue(newValue);
    };

    return (
        <>
            <div className="w3-padding bg-secondary w3-text-white">
                {active.name+"("+active.dir+")"}
            </div>
            <Box sx={{ width: '100%', typography: 'body1' }}>
                <Box sx={{ borderBottom: 1, borderColor: 'divider' }}>
                    <Tabs value={value} onChange={handleChange} aria-label="lab API tabs example">
                        <Tab label="Browse" {...a11yProps(0)} style={{textTransform:"none"}} />
                        <Tab label="Sql" {...a11yProps(1)} style={{textTransform:"none"}} />
                        <Tab label="Export" {...a11yProps(2)} style={{textTransform:"none"}} />
                        <Tab label="Import" {...a11yProps(3)} style={{textTransform:"none"}} />
                        <Tab label="Operations" {...a11yProps(4)} style={{textTransform:"none"}} />
                    </Tabs>
                </Box>
                <TabPanel value={value} index={0}>
                    <DataView/>
                </TabPanel>
                <TabPanel value={value} index={1}>
                    <h3>Accidents Reports</h3>
                </TabPanel>
                <TabPanel value={value} index={2}>
                    <h3>Travel</h3>
                </TabPanel>
                <TabPanel value={value} index={3}>
                    <h3>Supervisor Inspections</h3>
                </TabPanel>
                <TabPanel value={value} index={4}>
                    <h3>Supervisor Inspections</h3>
                </TabPanel>
            </Box>
        </>
    )
}

function DataView(){
    const {active,setActive} = useContext(Context);
    const [data,setData] = useState({
        cols:[],
        rows:[]
    });
    const [page, setPage] = React.useState(0);
    const [rowsPerPage, setRowsPerPage] = React.useState(15);
    const [sql,setSql] = useState("SELECT * FROM "+active.table);
    const [previousQueries,setPreviousQueries] = useState([]);

    const handleChangePage = (event, newPage) => {
        setPage(newPage);
    };
    
    const handleChangeRowsPerPage = (event) => {
        setRowsPerPage(parseInt(event.target.value, 10));
        setPage(0);
    };

    const getData = () => {
        $.get("api/", {getTableData:active.table, dir:active.dir, database:active.name}, function(res){
            setData({...data, ...res});
        })
    }

    const getPreviousQueries = () => {
        $.get("api/", {getPrevious:active.dir, database:active.name}, function(response){
            try{
                //Toast(response+" "+active.dir+" "+active.name);
                let res = JSON.parse(response);
                setPreviousQueries(res);
            }
            catch(E){
                alert(E.toString()+response);
            }
        })
    }

    useEffect(()=>{
        getData();
        getPreviousQueries();

        const listener = (event) => {
            var receivedValue = event.data;
            console.log("Received value from iframe:", receivedValue);
            if (typeof receivedValue == "string"){
                try{
                    let json = JSON.parse(receivedValue);
                    $.get("api/", {runQuery:json.query,table:active.table, dir:active.dir, database:active.name}, function(response){
                        try{
                            let res = JSON.parse(response);
                            Toast("Loaded")
                            setData(res);
                        }
                        catch(E){
                            alert(response);
                        }
                    });

                    //save the query to history
                }
                catch(E){
                    alert(E.toString()+ receivedValue);
                }
            }
        }

        window.addEventListener('message', listener);

        return ()=>{
            window.removeEventListener('message', listener);
        }
    }, []);

    const runQuery = (event) => {
        $.get("api/", {runQuery:sql,table:active.table, dir:active.dir, database:active.name}, function(response){
            try{
                let res = JSON.parse(response);
                Toast("Loaded")
                setData(res);
            }
            catch(E){
                alert(response);
            }
        })
    }

    const sendQuery = (query) => {
        var iframe = document.getElementById('code');

        // Send the value to the iframe
        iframe.contentWindow.postMessage(JSON.stringify({type:"query", query:query}), 'http://localhost/songs/sqlite2/sample.php');
    }

    const sendRun = () => {
        // Reference to the iframe element
        var iframe = document.getElementById('code');

        // Value you want to send
        var valueToSend = "run";

        // Send the value to the iframe
        iframe.contentWindow.postMessage(JSON.stringify({type:"command", command:valueToSend}), 'http://localhost/songs/sqlite2/sample.php');
    }

    const history = () => {
        //
    }

    useEffect(()=>{
        getData();
    }, [active]);

    return (
        <>
            <Box sx={{m:2}} className="">
                <Box sx={{p:2,mb:2,display:"none"}} className="border rounded">
                    <textarea style={{width:"100%",border:"none", fontFamily:"consolas"}} rows={1} value={sql} onChange={e=>setSql(e.target.value)} />
                    <pre id="editor"></pre>
                    
                </Box>
                <div className="w3-row">
                    <div className="w3-col m8">
                        <iframe src={"sample.php?table="+active.table} id="code" style={{width:"100%",height:"100px",border:"none",marginBottom:"10px"}}></iframe>
                        <Button sx={{textTransform:"none",display:"none"}} size="small" variant="outlined" onClick={runQuery}>Run Query</Button>
                        <Button sx={{textTransform:"none",ml:2}} color="error" size="small" variant="outlined" onClick={sendRun}>Run Query</Button>
                        <Button sx={{textTransform:"none",ml:2}} size="small" variant="outlined" onClick={history}>History</Button>
                    </div>
                    <div className="w3-col m4 pl-2 pt-2">
                        <b className="block">History</b>
                        <div>
                            {previousQueries.map((row,index)=>(
                                <div className="w3-padding-small w3-hover-light-grey pointer w3-border-bottom" onClick={e=>{
                                    sendQuery(row.query);
                                }}>{row.query}</div>
                            ))}
                        </div>
                    </div>
                </div>
                
            </Box>
            <Paper sx={{m:2}}>
                <Box sx={{p:1}}>
                    <select>
                        {data.cols.map((col,index)=>(
                            <option key={col}>{col}</option>
                        ))}
                    </select>
                    <input type="text" placeholder="Search" />
                </Box>
                <Table>
                    <TableHead>
                        <TableRow>
                            <TableCell></TableCell>
                            <TableCell>Actions</TableCell>
                            {data.cols.map((col,index)=>(
                                <TableCell key={col}>{col}</TableCell>
                            ))}
                        </TableRow>
                    </TableHead>
                    <TableBody>
                        {data.rows.slice(rowsPerPage*page, (page+1)*rowsPerPage).map((row,index)=>(
                            <TableRow hover key={index}>
                                <TableCell sx={{p:1}} align="center">
                                    <input type="checkbox"/>
                                </TableCell>
                                <TableCell padding="none">
                                    <Link sx={{pr:1}} href="#">Edit</Link>
                                    <Link sx={{pr:1}} color="error" href="#">Delete</Link>
                                </TableCell>
                                {data.cols.map((col,index)=>(
                                    <TableCell padding="none" key={"r"+index}>{row[col].length > 60 ? row[col].substr(0,60)+"..":row[col]}</TableCell>
                                ))}
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
                <TablePagination
                    rowsPerPageOptions={[10, 15, 25, 50, 100, 200, 500]}
                    component="div"
                    count={data.rows.length}
                    rowsPerPage={rowsPerPage}
                    page={page}
                    onPageChange={handleChangePage}
                    onRowsPerPageChange={handleChangeRowsPerPage}
                    />
            </Paper>
        </>
    )
}

function loadAceLinters() {
    if (typeof  define == "function" && define.amd) {
         require([
            "https://mkslanc.github.io/ace-linters/build/ace-linters.js"
        ], function(m) {
            addLinters(m.LanguageProvider);
        });
    } else {
        require("ace/lib/net").loadScript(
            "https://mkslanc.github.io/ace-linters/build/ace-linters.js", 
            function() {
                addLinters(window.LanguageProvider);
            }
        ) 
    }
    function addLinters(LanguageProvider) {
        var languageProvider = LanguageProvider.fromCdn("https://mkslanc.github.io/ace-linters/build", {
            functionality: {
                hover: true,
                completion: {
                    overwriteCompleters: false
                },
                completionResolve: true,
                format: true,
                documentHighlights: true,
                signatureHelp: false
            }
        });
        window.languageProvider = languageProvider;
        document.querySelectorAll(".ace_editor").forEach(function(el) {
            var editor = el.env && el.env.editor;
            if (editor) {
                editor.setOption("enableBasicAutocompletion", true)
                languageProvider.registerEditor(editor);
            }
        });
    }
}