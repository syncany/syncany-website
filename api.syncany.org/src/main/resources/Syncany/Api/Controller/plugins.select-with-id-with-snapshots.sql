select * from plugins
where
	    pluginId=:pluginId
	and (pluginOperatingSystem='all' or pluginOperatingSystem=:pluginOperatingSystem)
	and (pluginArchitecture='all' or pluginArchitecture=:pluginArchitecture)
order by id desc