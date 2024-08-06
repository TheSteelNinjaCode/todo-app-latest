const fs = require("fs");
const path = require("path");

// Path to your JSON configuration file
const configFilePath = path.join(__dirname, "..", "prisma-php.json");

// Use the parent directory name as the new project name
const newProjectName = path.basename(path.join(__dirname, ".."));

// Function to update the project name and paths in the JSON config
function updateProjectNameInConfig(filePath, newProjectName) {
  const filePathDir = path.dirname(filePath);
  fs.readFile(filePath, "utf8", (err, data) => {
    if (err) {
      console.error("Error reading the JSON file:", err);
      return;
    }

    let config = JSON.parse(data);

    // Update the projectName
    config.projectName = newProjectName;

    // Update other paths
    config.projectRootPath = filePathDir;

    const targetPath = getTargetPath(filePathDir, config.phpEnvironment);

    config.bsTarget = `http://localhost${targetPath}`;
    config.bsPathRewrite["^/"] = targetPath;

    // Save the updated config back to the JSON file
    fs.writeFile(filePath, JSON.stringify(config, null, 2), "utf8", (err) => {
      if (err) {
        console.error("Error writing the updated JSON file:", err);
        return;
      }
      console.log(
        "The project name, PHP path, and other paths have been updated successfully."
      );
    });
  });
}

// Function to determine the target path for browser-sync
function getTargetPath(fullPath, environment) {
  const normalizedPath = path.normalize(fullPath);
  const webDirectories = {
    XAMPP: path.join("htdocs"),
    WAMP: path.join("www"),
    MAMP: path.join("htdocs"),
    LAMP: path.join("var", "www", "html"),
    LEMP: path.join("usr", "share", "nginx", "html"),
    AMPPS: path.join("www"),
    UniformServer: path.join("www"),
    EasyPHP: path.join("data", "localweb"),
  };

  const webDir = webDirectories[environment.toUpperCase()];
  if (!webDir) {
    throw new Error(`Unsupported environment: ${environment}`);
  }

  const indexOfWebDir = normalizedPath
    .toLowerCase()
    .indexOf(path.normalize(webDir).toLowerCase());
  if (indexOfWebDir === -1) {
    throw new Error(`Web directory not found in path: ${webDir}`);
  }

  const startIndex = indexOfWebDir + webDir.length;
  const subPath = normalizedPath.slice(startIndex);
  const safeSeparatorRegex = new RegExp(
    path.sep.replace(/[-\/\\^$*+?.()|[\]{}]/g, "\\$&"),
    "g"
  );
  const finalPath = subPath.replace(safeSeparatorRegex, "/") + "/";

  return finalPath;
}

// Run the function with your config file path and the new project name
updateProjectNameInConfig(configFilePath, newProjectName);
