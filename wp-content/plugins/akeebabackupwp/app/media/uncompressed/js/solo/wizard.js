/**
 * @package   solo
 * @copyright Copyright (c)2014-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Object initialisation
if (typeof akeeba === "undefined")
{
    var akeeba = {};
}

if (typeof akeeba.Wizard === "undefined")
{
    akeeba.Wizard = {
        execTimes:   [30, 25, 20, 14, 7, 5, 3],
        blockSizes:  [240, 200, 160, 80, 40, 16, 4, 2, 1],
        translation: {}
    }
}

/**
 * Boot up the Configuration Wizard benchmarking process
 */
akeeba.Wizard.boot = function ()
{
    akeeba.Wizard.execTimes  = [30, 25, 20, 14, 7, 5, 3];
    akeeba.Wizard.blockSizes = [480, 400, 240, 200, 160, 80, 40, 16, 4, 2, 1];

    // Show GUI
    document.getElementById("backup-progress-pane").style.display = "block";
    akeeba.Backup.resetTimeoutBar();

    // Before continuing, perform a call to the ping method, so Akeeba Backup knowns that it was configured
    akeeba.System.doAjax(
        {akact: "ping"},
        function ()
        {
            akeeba.Wizard.minExec();
        },
        function ()
        {
        },
        false,
        10000
    );
};

/**
 * Determine the optimal Minimum Execution Time
 *
 * @param   seconds     How many seconds to test
 * @param   repetition  Which try is this?
 */
akeeba.Wizard.minExec = function (seconds, repetition)
{
    if (seconds == null)
    {
        seconds = 0;
    }
    if (repetition == null)
    {
        repetition = 0;
    }

    akeeba.Backup.resetTimeoutBar();
    akeeba.Backup.startTimeoutBar((2 * seconds + 5) * 1000, 100);

    document.getElementById("backup-substep").textContent =
        akeeba.System.Text._("COM_AKEEBA_CONFWIZ_UI_MINEXECTRY").replace("%s", seconds.toFixed(1));

    var stepElement       = document.getElementById("step-minexec");
    stepElement.className = "akeeba-label--teal";

    akeeba.System.doAjax(
        {akact: "minexec", "seconds": seconds},
        function (msg)
        {
            // The ping was successful. Add a repetition count.
            repetition++;
            if (repetition < 3)
            {
                // We need more repetitions
                akeeba.Wizard.minExec(seconds, repetition);
            }
            else
            {
                // Three repetitions reached. Success!
                akeeba.Wizard.minExecApply(seconds);
            }
        },
        function ()
        {
            // We got a failure. Add half a second
            seconds += 0.5;

            if (seconds > 20)
            {
                // Uh-oh... We exceeded our maximum allowance!
                document.getElementById("backup-progress-pane").style.display = "none";
                document.getElementById("error-panel").style.display          = "block";
                document.getElementById("backup-error-message").textContent   =
                    akeeba.System.Text._("COM_AKEEBA_CONFWIZ_UI_CANTDETERMINEMINEXEC");
            }
            else
            {
                akeeba.Wizard.minExec(seconds, 0);
            }
        },
        false,
        (2 * seconds + 5) * 1000
    );
};

/**
 * Applies the AJAX preference and the minimum execution time determined in the previous steps
 *
 * @param   seconds  The minimum execution time, in seconds
 */
akeeba.Wizard.minExecApply = function (seconds)
{
    akeeba.Backup.resetTimeoutBar();
    akeeba.Backup.startTimeoutBar(25000, 100);

    document.getElementById("backup-substep").textContent = akeeba.System.Text._("COM_AKEEBA_CONFWIZ_UI_SAVEMINEXEC");

    akeeba.System.doAjax(
        {akact: "applyminexec", "minexec": seconds},
        function (msg)
        {
            var stepElement       = document.getElementById("step-minexec");
            stepElement.className = stepElement.className = "akeeba-label--green";

            akeeba.Wizard.directories();
        },
        function ()
        {
            // Unsuccessful call. Oops!
            document.getElementById("backup-progress-pane").style.display = "none";
            document.getElementById("error-panel").style.display          = "block";
            document.getElementById("backup-error-message").textContent   =
                akeeba.System.Text._("COM_AKEEBA_CONFWIZ_UI_CANTSAVEMINEXEC");
        },
        false
    );
};

/**
 * Automatically determine the optimal output and temporary directories,
 * then make sure they are writable
 */
akeeba.Wizard.directories = function ()
{
    akeeba.Backup.resetTimeoutBar();
    akeeba.Backup.startTimeoutBar(10000, 100);

    document.getElementById("backup-substep").innerHTML = "";

    var stepElement       = document.getElementById("step-directory");
    stepElement.className = "akeeba-label--teal";

    akeeba.System.doAjax(
        {akact: "directories"},
        function (msg)
        {
            if (msg)
            {
                var stepElement       = document.getElementById("step-directory");
                stepElement.className = stepElement.className = "akeeba-label--green";

                akeeba.Wizard.database();
            }
            else
            {
                document.getElementById("backup-progress-pane").style.display = "none";
                document.getElementById("error-panel").style.display          = "block";
                document.getElementById("backup-error-message").textContent   =
                    akeeba.System.Text._("COM_AKEEBA_CONFWIZ_UI_CANTFIXDIRECTORIES");
            }
        },
        function ()
        {
            document.getElementById("backup-progress-pane").style.display = "none";
            document.getElementById("error-panel").style.display          = "block";
            document.getElementById("backup-error-message").textContent   =
                akeeba.System.Text._("COM_AKEEBA_CONFWIZ_UI_CANTFIXDIRECTORIES");
        },
        false
    );
};

/**
 * Determine the optimal database dump options, analyzing the site's database
 */
akeeba.Wizard.database = function ()
{
    akeeba.Backup.resetTimeoutBar();
    akeeba.Backup.startTimeoutBar(30000, 50);

    document.getElementById("backup-substep").innerHTML = "";
    var stepElement                                     = document.getElementById("step-dbopt");
    stepElement.className                               = "akeeba-label--teal";

    akeeba.System.doAjax(
        {akact: "database"},
        function (msg)
        {
            if (msg)
            {
                var stepElement       = document.getElementById("step-dbopt");
                stepElement.className = stepElement.className = "akeeba-label--green";

                akeeba.Wizard.maxExec();
            }
            else
            {
                document.getElementById("backup-progress-pane").style.display = "none";
                document.getElementById("error-panel").style.display          = "block";
                document.getElementById("backup-error-message").textContent   =
                    akeeba.System.Text._("COM_AKEEBA_CONFWIZ_UI_CANTDBOPT");
            }
        },
        function ()
        {
            document.getElementById("backup-progress-pane").style.display = "none";
            document.getElementById("error-panel").style.display          = "block";
            document.getElementById("backup-error-message").textContent   =
                akeeba.System.Text._("COM_AKEEBA_CONFWIZ_UI_CANTDBOPT");
        },
        false
    );
};

/**
 * Determine the optimal maximum execution time which doesn't cause a timeout or server error
 */
akeeba.Wizard.maxExec = function ()
{
    var exec_time = array_shift(akeeba.Wizard.execTimes);

    if (empty(akeeba.Wizard.execTimes) || (exec_time == null))
    {
        // Darn, we ran out of options
        document.getElementById("backup-progress-pane").style.display = "none";
        document.getElementById("error-panel").style.display          = "block";
        document.getElementById("backup-error-message").textContent   =
            akeeba.System.Text._("COM_AKEEBA_CONFWIZ_UI_EXECTOOLOW");

        return;
    }

    akeeba.Backup.resetTimeoutBar();
    akeeba.Backup.startTimeoutBar((exec_time * 1.2) * 1000, 80);

    var stepElement       = document.getElementById("step-maxexec");
    stepElement.className = "akeeba-label--teal";

    document.getElementById("backup-substep").textContent =
        akeeba.System.Text._("COM_AKEEBA_CONFWIZ_UI_MINEXECTRY").replace("%s", exec_time.toFixed(0));

    akeeba.System.doAjax(
        {akact: "maxexec", "seconds": exec_time},
        function (msg)
        {
            if (msg)
            {
                // Success! Save this value.
                akeeba.Wizard.maxExecApply(exec_time);
            }
            else
            {
                // Uh... we have to try something lower than that
                akeeba.Wizard.maxExec();
            }
        },
        function ()
        {
            // Uh... we have to try something lower than that
            akeeba.Wizard.maxExec();
        },
        false,
        38000 // Maximum time to wait: 38 seconds
    );
};

/**
 * Apply the maximum execution time
 *
 * @param   seconds  The number of max execution time (in seconds) we found that works on the server
 */
akeeba.Wizard.maxExecApply = function (seconds)
{
    akeeba.Backup.resetTimeoutBar();
    akeeba.Backup.startTimeoutBar(10000, 100);

    document.getElementById("backup-substep").textContent = akeeba.System.Text._("COM_AKEEBA_CONFWIZ_UI_SAVINGMAXEXEC");

    akeeba.System.doAjax(
        {akact: "applymaxexec", "seconds": seconds},
        function ()
        {
            var stepElement       = document.getElementById("step-maxexec");
            stepElement.className = stepElement.className = "akeeba-label--green";

            akeeba.Wizard.partSize();
        },
        function ()
        {
            document.getElementById("backup-progress-pane").style.display = "none";
            document.getElementById("error-panel").style.display          = "block";
            document.getElementById("backup-error-message").textContent   =
                akeeba.System.Text._("COM_AKEEBA_CONFWIZ_UI_CANTSAVEMAXEXEC");
        }
    );
};

/**
 * Try to find the best part size for split archives which works on this server
 */
akeeba.Wizard.partSize = function ()
{
    akeeba.Backup.resetTimeoutBar();

    var block_size = array_shift(akeeba.Wizard.blockSizes);
    if (empty(akeeba.Wizard.blockSizes) || (block_size == null))
    {
        // Uh... I think you are running out of disk space, dude
        document.getElementById("backup-progress-pane").style.display = "none";
        document.getElementById("error-panel").style.display          = "block";
        document.getElementById("backup-error-message").textContent   =
            akeeba.System.Text._("COM_AKEEBA_CONFWIZ_UI_CANTDETERMINEPARTSIZE");

        return;
    }

    var part_size = block_size / 8; // Translate to Mb

    akeeba.Backup.startTimeoutBar(30000, 100);
    document.getElementById("backup-substep").textContent =
        akeeba.System.Text._("COM_AKEEBA_CONFWIZ_UI_PARTSIZE").replace("%s", part_size.toFixed(3));

    var stepElement       = document.getElementById("step-splitsize");
    stepElement.className = "akeeba-label--teal";

    akeeba.System.doAjax(
        {akact: "partsize", blocks: block_size},
        function (msg)
        {
            if (msg)
            {
                // We are done
                var stepElement       = document.getElementById("step-splitsize");
                stepElement.className = stepElement.className = "akeeba-label--green";

                akeeba.Wizard.done();
            }
            else
            {
                // Let's try the next (lower) value
                akeeba.Wizard.partSize();
            }
        },
        function (msg)
        {
            // The server blew up on our face. Let's try the next (lower) value.
            akeeba.Wizard.partSize();
        },
        false,
        60000
    );
};

/**
 * The configuration wizard is done
 */
akeeba.Wizard.done = function ()
{
    document.getElementById("backup-progress-pane").style.display = "none";
    document.getElementById("backup-complete").style.display      = "block";
};

akeeba.Wizard.autodetect = function (folder)
{
    var data = {
        akact:  "pythia",
        folder: folder
    };

    document.getElementById("varakeeba.advanced.embedded_installer").value = "angie-generic";

    akeeba.System.doAjax(data, function (retData)
    {
        if (typeof retData !== "object")
        {
            return;
        }

        var driver = retData.database.driver;

        document.getElementById("varakeeba.platform.scripttype").value         = retData.cms;
        document.getElementById("varakeeba.advanced.embedded_installer").value = retData.installer;
        document.getElementById("var[akeeba.platform.dbdriver]").value         = driver;
        document.getElementById("var[akeeba.platform.dbhost]").value           = retData.database.host;
        document.getElementById("var[akeeba.platform.dbport]").value           = retData.database.port;
        document.getElementById("var[akeeba.platform.dbusername]").value       = retData.database.username;
        document.getElementById("var[akeeba.platform.dbpassword]").value       = retData.database.password;
        document.getElementById("var[akeeba.platform.dbname]").value           = retData.database.name;
        document.getElementById("var[akeeba.platform.dbprefix]").value         = retData.database.prefix;

        document.getElementById("pythiaExtradirs").innerHTML = retData.extradirs.join("<br/>");

        var html = [];
        var dbinfo;

        for (var index = 0; index < retData.extradb.length; index++)
        {
            var db = retData.extradb[index];

            dbinfo = "<strong>Host</strong>: " + db.host;
            dbinfo += " <strong>Db</strong>: " + db.name;
            dbinfo += " <strong>User</strong>: " + db.username;
            dbinfo += " <strong>Prefix</strong>: " + db.prefix;

            html.push(dbinfo);
        }

        document.getElementById("pythiaExtradb").innerHTML = html.join("<br/>");

        akeeba.System.triggerEvent(document.querySelector("select[id*=\"akeeba.platform.dbdriver\"]"), "change");
    });

    return false;
};

akeeba.Wizard.onDatabaseDriverChange = function (e)
{
    var newDriver = this.value.toLowerCase();

    document.getElementById("host-wrapper").style.display   = "grid";
    document.getElementById("port-wrapper").style.display   = "grid";
    document.getElementById("user-wrapper").style.display   = "grid";
    document.getElementById("pass-wrapper").style.display   = "grid";
    document.getElementById("name-wrapper").style.display   = "grid";
    document.getElementById("prefix-wrapper").style.display = "grid";

    if ((newDriver == "sqlite") || (newDriver == "none"))
    {
        document.getElementById("host-wrapper").style.display = "none";
        document.getElementById("port-wrapper").style.display = "none";
        document.getElementById("user-wrapper").style.display = "none";
        document.getElementById("pass-wrapper").style.display = "none";

        document.querySelector("input[id*=\"akeeba.platform.dbhost\"]").value     = "";
        document.querySelector("input[id*=\"akeeba.platform.dbport\"]").value     = "";
        document.querySelector("input[id*=\"akeeba.platform.dbusername\"]").value = "";
        document.querySelector("input[id*=\"akeeba.platform.dbpassword\"]").value = "";
    }

    if (newDriver == "none")
    {
        document.getElementById("name-wrapper").style.display   = "none";
        document.getElementById("prefix-wrapper").style.display = "none";

        document.querySelector("input[id*=\"akeeba.platform.dbname\"]").value   = "";
        document.querySelector("input[id*=\"akeeba.platform.dbprefix\"]").value = "";
    }
};

akeeba.Wizard.onBtnBrowseClick = function (e)
{
    var element = document.getElementById("var[akeeba.platform.newroot]");
    var folder  = element.value;

    akeeba.Configuration.onBrowser(folder, element);

    e.preventDefault();

    return false;
};

akeeba.Wizard.onBtnPythiaClick = function (e)
{
	e.preventDefault();

    var element = document.getElementById("var[akeeba.platform.newroot]");
    var folder  = element.value;

    akeeba.Wizard.autodetect(folder);

    return false;
};

akeeba.System.documentReady(function ()
{
    // INITIAL PAGE (layout=wizard)
    var elDbDriver = document.querySelector("select[id*=\"akeeba.platform.dbdriver\"]");

    akeeba.System.addEventListener(document.getElementById("btnBrowse"), "click", akeeba.Wizard.onBtnBrowseClick);
    akeeba.System.addEventListener(document.getElementById("btnPythia"), "click", akeeba.Wizard.onBtnPythiaClick);
    akeeba.System.addEventListener(elDbDriver, "change", akeeba.Wizard.onDatabaseDriverChange);
    akeeba.System.triggerEvent(elDbDriver, "change");

    // MAIN PAGE (layout=default)
    if (elDbDriver === null)
    {
        akeeba.Wizard.boot();
    }
});